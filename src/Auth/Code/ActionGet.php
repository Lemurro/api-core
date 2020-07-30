<?php

/**
 * Получение кода аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.07.2020
 */

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
use ORM;
use Pimple\Container;
use RuntimeException;

/**
 * Class ActionGet
 *
 * @package Lemurro\Api\Core\Auth\Code
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

    /**
     * ActionGet constructor.
     *
     * @param Container $dic
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->mailer = $dic['mailer'];
        $this->sms = $dic['sms'];
        $this->datetimenow = $dic['datetimenow'];
        $this->code_cleaner = new Code();
        $this->user_finder = new FindUser();
        $this->user_inserter = new InsertUser($dic);
        $this->phone_validator = new Phone();
        $this->log = LoggerFactory::create('Auth');
    }

    /**
     * Выполним действие
     *
     * @param string $auth_id Номер телефона или электронная почта
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
     */
    public function run($auth_id): array
    {
        $this->code_cleaner->clear($auth_id);

        try {
            $user = $this->findUser($auth_id);

            if ((int) $user['locked'] === 1) {
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
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.07.2020
     */
    private function findUser($auth_id): array
    {
        $user = $this->user_finder->run($auth_id);

        if (is_array($user) && !empty($user)) {
            return $user;
        }

        if (!SettingsAuth::CAN_REGISTRATION_USERS) {
            throw new RuntimeException('Пользователь не найден', 404);
        }

        $insert_user = $this->user_inserter->run([
            'auth_id' => $auth_id,
        ]);

        if (isset($insert_user['errors'])) {
            Response::errorToException($insert_user);
        }

        return $insert_user['data'];
    }

    /**
     * Защита от брутфорса
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.06.2020
     */
    private function bruteForceProtection(): void
    {
        $lastday = Carbon::now()->subDay()->toDateTimeString();

        $count = ORM::for_table('auth_codes_lasts')
            ->where_equal('user_id', $this->user_id)
            ->where_gte('created_at', $lastday)
            ->count();
        if ($count >= SettingsAuth::ATTEMPTS_PER_DAY) {
            throw new RuntimeException('Попытка брутфорса (user_id: ' . $this->user_id . ')', 403);
        }
    }

    /**
     * Генерация уникального кода
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
     */
    private function generateCode(): void
    {
        $exist_codes = $this->getExistCodes();

        $this->secret = RandomNumber::generate(4);
        while (in_array($this->secret, $exist_codes)) {
            $this->secret = RandomNumber::generate(4);
        }
    }

    /**
     * Получение существующих кодов
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
     */
    private function getExistCodes(): array
    {
        $exist_codes = [];

        $auth_codes = ORM::for_table('auth_codes')
            ->select('code')
            ->find_array();

        if (is_array($auth_codes) && !empty($auth_codes)) {
            foreach ($auth_codes as $item) {
                $exist_codes[] = $item['code'];
            }
        }

        return $exist_codes;
    }

    /**
     * Сохранение кода в БД
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.06.2020
     */
    private function saveCode(): void
    {
        try {
            ORM::get_db()->beginTransaction();

            $auth_code = ORM::for_table('auth_codes')->create();
            $auth_code->auth_id = $this->auth_id;
            $auth_code->code = $this->secret;
            $auth_code->user_id = $this->user_id;
            $auth_code->created_at = $this->datetimenow;
            $auth_code->save();

            $auth_code_last = ORM::for_table('auth_codes_lasts')->create();
            $auth_code_last->user_id = $this->user_id;
            $auth_code_last->created_at = $this->datetimenow;
            $auth_code_last->save();

            ORM::get_db()->commit();
        } catch (Exception $e) {
            ORM::get_db()->rollBack();

            LogException::write($this->log, $e);

            throw new RuntimeException('Произошла ошибка при сохранении в БД', 500);
        }
    }

    /**
     * Отправка кода пользователю
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
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
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
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
                '[SECRET]'   => $this->secret,
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
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.06.2020
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
