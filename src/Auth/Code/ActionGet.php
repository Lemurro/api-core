<?php

namespace Lemurro\Api\Core\Auth\Code;

use Carbon\Carbon;
use Exception;
use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Lemurro\Api\Core\Helpers\Mailer;
use Lemurro\Api\Core\Helpers\RandomNumber;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Helpers\SMS\Phone;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use Lemurro\Api\Core\Users\ActionInsert as InsertUser;
use Lemurro\Api\Core\Users\Find as FindUser;
use Monolog\Logger;
use Pimple\Container;
use RuntimeException;

/**
 * Получение кода аутентификации
 */
class ActionGet extends Action
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var SMS
     */
    private $sms;

    /**
     * @var string
     */
    private $datetimenow;

    /**
     * @var Code
     */
    private $code_cleaner;

    /**
     * @var FindUser
     */
    private $user_finder;

    /**
     * @var InsertUser
     */
    private $user_inserter;

    /**
     * @var Phone
     */
    private $phone_validator;

    /**
     * @var string
     */
    private $auth_id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var integer
     */
    private $secret;

    /**
     * @var Logger
     */
    private $log;

    private string $ip;

    /**
     * @param Container $dic
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->mailer = $dic['mailer'];
        $this->sms = $dic['sms'];
        $this->datetimenow = $dic['datetimenow'];
        $this->code_cleaner = new Code($this->dbal);
        $this->user_finder = new FindUser($this->dbal);
        $this->user_inserter = new InsertUser($dic);
        $this->phone_validator = new Phone();
        $this->log = LoggerFactory::create('Auth');
    }

    /**
     * Получение кода аутентификации
     *
     * @param string $auth_id Номер телефона или электронная почта
     * @param string $ip
     *
     * @return array
     */
    public function run($auth_id, $ip = ''): array
    {
        if (empty($auth_id)) {
            return Response::error400('Отсутствует параметр "auth_id"');
        }

        $this->code_cleaner->clear($auth_id);
        $this->ip = $ip;

        try {
            $user = $this->findUser($auth_id);

            if ((int)$user['locked'] === 1) {
                throw new RuntimeException('Пользователь заблокирован и недоступен для входа', 403);
            }

            $this->auth_id = $user['auth_id'];
            $this->user_id = $user['id'];

            $this->bruteForceProtection();
            $this->generateCode();
            $this->saveCode();
        } catch (Exception $e) {
            LogException::write($this->log, $e);

            return Response::error500('При получении кода произошла ошибка, пожалуйста обратитесь к администратору');
        }

        return $this->sendCode();
    }

    /**
     * Поиск пользователя
     *
     * @param string $auth_id
     *
     * @return array
     */
    private function findUser($auth_id): array
    {
        $user = $this->user_finder->getByAuthId((string)$auth_id);
        if (!empty($user)) {
            return $user;
        }

        if (!SettingsAuth::CAN_REGISTRATION_USERS) {
            throw new RuntimeException('Пользователь не найден', 404);
        }

        $insert_user = $this->user_inserter->run([
            'auth_id' => $auth_id,
        ]);

        if (isset($insert_user['errors'])) {
            throw new RuntimeException($insert_user['errors'][0]['title'], 500);
        }

        return $insert_user['data'];
    }

    /**
     * Защита от брутфорса
     */
    private function bruteForceProtection(): void
    {
        $lastday = Carbon::now()->subDay()->toDateTimeString();

        $count = $this->dbal->fetchOne('SELECT COUNT(user_id) AS cnt FROM auth_codes_lasts WHERE user_id = ? AND created_at >= ?', [$this->user_id, $lastday]);
        if ($count >= SettingsAuth::ATTEMPTS_PER_DAY) {
            throw new RuntimeException('Попытка брутфорса (user_id: ' . $this->user_id . ')', 403);
        }
    }

    /**
     * Генерация уникального кода
     */
    private function generateCode(): void
    {
        $exist_codes = $this->dbal->fetchAllKeyValue('SELECT id, code FROM auth_codes');

        $this->secret = RandomNumber::generate(4);
        while (in_array($this->secret, $exist_codes)) {
            $this->secret = RandomNumber::generate(4);
        }
    }

    /**
     * Сохранение кода в БД
     */
    private function saveCode(): void
    {
        $this->dbal->transactional(function (): void {
            $this->dbal->insert('auth_codes', [
                'auth_id' => $this->auth_id,
                'ip' => $this->ip,
                'code' => $this->secret,
                'user_id' => $this->user_id,
                'created_at' => $this->datetimenow,
            ]);

            $this->dbal->insert('auth_codes_lasts', [
                'user_id' => $this->user_id,
                'created_at' => $this->datetimenow,
            ]);
        });
    }

    /**
     * Отправка кода пользователю
     *
     * @return array
     */
    private function sendCode(): array
    {
        if (!SettingsGeneral::PRODUCTION) {
            return Response::data([
                'message' => $this->secret,
            ]);
        }

        switch (SettingsAuth::TYPE) {
            case 'email':
                return $this->sendEmail();
                break;

            case 'phone':
                return $this->sendSms();
                break;

            case 'mixed':
                if ($this->phone_validator->isPhone($this->auth_id)) {
                    return $this->sendSms();
                } else {
                    return $this->sendEmail();
                }
                break;

            default:
                $this->log->warning('Неверный вид аутентификации "' . SettingsAuth::TYPE . '", проверьте настройки');

                return Response::error500('При получении кода произошла ошибка, пожалуйста обратитесь к администратору');
                break;
        }
    }

    /**
     * Отправка кода на электронную почту
     *
     * @return array
     */
    private function sendEmail()
    {
        $this->mailer->send(
            'AUTH_CODE',
            'Код для входа в приложение для пользователя: ' . $this->auth_id,
            [
                $this->auth_id,
            ],
            [
                '[APP_NAME]' => SettingsGeneral::APP_NAME,
                '[SECRET]' => $this->secret,
            ]
        );

        return Response::data([
            'message' => 'Письмо, с кодом для входа, успешно отправлено на указанную электронную почту',
        ]);
    }

    /**
     * Отправка кода в виде смс
     *
     * @return array
     */
    private function sendSms()
    {
        $this->sms->send(
            $this->auth_id,
            'Код для входа: ' . $this->secret . ', ' . SettingsGeneral::APP_NAME
        );

        return Response::data([
            'message' => 'СМС, с кодом для входа, отправлено на указанный номер телефона',
        ]);
    }
}
