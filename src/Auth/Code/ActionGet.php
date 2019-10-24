<?php
/**
 * Получение кода аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 24.10.2019
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Mailer;
use Lemurro\Api\Core\Helpers\RandomNumber;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use Lemurro\Api\Core\Users\ActionInsert as InsertUser;
use Lemurro\Api\Core\Users\Find as FindUser;
use ORM;

/**
 * Class ActionGet
 *
 * @package Lemurro\Api\Core\Auth\Code
 */
class ActionGet extends Action
{
    /**
     * Выполним действие
     *
     * @param string $auth_id Номер телефона или электронная почта
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function run($auth_id)
    {
        (new Code())->clear($auth_id);

        $user = (new FindUser())->run($auth_id);
        if (is_array($user) && empty($user)) {
            if (SettingsAuth::CAN_REGISTRATION_USERS) {
                $insert_user = (new InsertUser($this->dic))->run([
                    'auth_id' => $auth_id,
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
            return Response::error403('Пользователь заблокирован и недоступен для входа, пожалуйста обратитесь к администратору', false);
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

        $secret = RandomNumber::generate(4);
        while (in_array($secret, $all_codes)) {
            $secret = RandomNumber::generate(4);
        }

        $auth_code = ORM::for_table('auth_codes')->create();
        $auth_code->auth_id = $auth_id;
        $auth_code->code = $secret;
        $auth_code->user_id = $user['id'];
        $auth_code->created_at = $this->dic['datetimenow'];
        $auth_code->save();
        if (is_object($auth_code)) {
            if (SettingsGeneral::PRODUCTION) {
                switch (SettingsAuth::TYPE) {
                    case 'email':
                        $message = 'Письмо, с кодом для входа, успешно отправлено на указанную электронную почту';

                        /** @var Mailer $mailer */
                        $mailer = $this->dic['mailer'];

                        $template_name = 'AUTH_CODE';
                        $subject = 'Код для входа в приложение для пользователя: ' . $auth_id;
                        $email_tos = [$auth_id];
                        $template_data = ['[APP_NAME]' => SettingsGeneral::APP_NAME, '[SECRET]' => $secret];

                        $result = $mailer->send($template_name, $subject, $email_tos, $template_data);
                        break;

                    case 'phone':
                        $message = 'СМС, с кодом для входа, отправлено на указанный номер телефона';

                        /** @var SMS $sms */
                        $sms = $this->dic['sms'];

                        $result = $sms->send($auth_id, 'Код для входа: ' . $secret . ', ' . SettingsGeneral::APP_NAME);
                        break;

                    default:
                        return Response::error400('Неверный вид аутентификации, проверьте настройки');
                        break;
                }

                if ($result) {
                    return Response::data([
                        'message' => $message,
                    ]);
                } else {
                    return Response::error500('Произошла ошибка при отправке кода, попробуйте ещё раз');
                }
            } else {
                return Response::data([
                    'message' => $secret,
                ]);
            }
        } else {
            return Response::error500('Произошла ошибка при создании кода, попробуйте ещё раз');
        }
    }
}
