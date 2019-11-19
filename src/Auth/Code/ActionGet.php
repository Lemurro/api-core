<?php
/**
 * Получение кода аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Mailer;
use Lemurro\Api\Core\Helpers\RandomNumber;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Helpers\SMS\Phone;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use Lemurro\Api\Core\Users\ActionInsert as InsertUser;
use Lemurro\Api\Core\Users\Find as FindUser;
use ORM;
use Pimple\Container;

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
    protected $mailer;

    /**
     * @var SMS
     */
    protected $sms;

    /**
     * @var Code
     */
    protected $code_cleaner;

    /**
     * @var FindUser
     */
    protected $user_finder;

    /**
     * @var InsertUser
     */
    protected $user_inserter;

    /**
     * @var Phone
     */
    protected $phone_validator;

    /**
     * @var string
     */
    protected $auth_id;

    /**
     * @var integer
     */
    protected $secret;

    /**
     * ActionGet constructor.
     *
     * @param Container $dic
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->mailer = $dic['mailer'];
        $this->sms = $dic['sms'];
        $this->code_cleaner = new Code();
        $this->user_finder = new FindUser();
        $this->user_inserter = new InsertUser($dic);
        $this->phone_validator = new Phone();
    }

    /**
     * Выполним действие
     *
     * @param string $auth_id Номер телефона или электронная почта
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function run($auth_id)
    {
        $this->auth_id = $auth_id;

        $this->code_cleaner->clear($this->auth_id);

        $user = $this->user_finder->run($this->auth_id);
        if (is_array($user) && empty($user)) {
            if (SettingsAuth::CAN_REGISTRATION_USERS) {
                $insert_user = $this->user_inserter->run([
                    'auth_id' => $this->auth_id,
                ]);
                if (isset($insert_user['errors'])) {
                    return $insert_user;
                } else {
                    $user = $insert_user['data'];
                }
            } else {
                return Response::error404('Пользователь не найден');
            }
        }

        if ($user['locked'] == 1) {
            return Response::error403('Пользователь заблокирован и недоступен для входа, пожалуйста обратитесь к администратору',
                false);
        }

        $all_codes = [];
        $auth_codes = ORM::for_table('auth_codes')
            ->select('code')
            ->find_many();
        if (is_array($auth_codes) && count($auth_codes) > 0) {
            foreach ($auth_codes as $item) {
                $all_codes[] = $item->code;
            }
        }

        $this->secret = RandomNumber::generate(4);
        while (in_array($this->secret, $all_codes)) {
            $this->secret = RandomNumber::generate(4);
        }

        $auth_code = ORM::for_table('auth_codes')->create();
        $auth_code->auth_id = $this->auth_id;
        $auth_code->code = $this->secret;
        $auth_code->user_id = $user['id'];
        $auth_code->created_at = $this->date_time_now;
        $auth_code->save();
        if (is_object($auth_code)) {
            if (SettingsGeneral::PRODUCTION) {
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
                        return Response::error400('Неверный вид аутентификации, проверьте настройки');
                        break;
                }
            } else {
                return Response::data([
                    'message' => $this->secret,
                ]);
            }
        } else {
            return Response::error500('Произошла ошибка при создании кода, попробуйте ещё раз');
        }
    }

    /**
     * Отправка кода на электронную почту
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    protected function sendEmail()
    {
        $result = $this->mailer->send(
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

        if ($result) {
            return Response::data([
                'message' => 'Письмо, с кодом для входа, успешно отправлено на указанную электронную почту',
            ]);
        } else {
            return Response::error500('Произошла ошибка при отправке кода, попробуйте ещё раз');
        }
    }

    /**
     * Отправка кода в виде смс
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    protected function sendSms()
    {
        $result = $this->sms->send(
            $this->auth_id,
            'Код для входа: ' . $this->secret . ', ' . SettingsGeneral::APP_NAME
        );

        if ($result) {
            return Response::data([
                'message' => 'СМС, с кодом для входа, отправлено на указанный номер телефона',
            ]);
        } else {
            return Response::error500('Произошла ошибка при отправке кода, попробуйте ещё раз');
        }
    }
}
