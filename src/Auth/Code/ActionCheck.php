<?php
/**
 * Проверка кода аутентификации
 *
 * @version 12.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use ORM;

/**
 * Class ActionCheck
 *
 * @package Lemurro\Api\Core\Auth\Code
 */
class ActionCheck extends Action
{
    /**
     * Выполним действие
     *
     * @param string $auth_id     Номер телефона или электронная почта
     * @param string $auth_code   Код из СМС или письма
     * @param array  $device_info Информация об устройстве
     *
     * @return array
     *
     * @version 12.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($auth_id, $auth_code, $device_info)
    {
        $cleaner = new Code();

        $cleaner->clear();

        $auth = ORM::for_table('auth_codes')
            ->where_equal('auth_id', $auth_id)
            ->find_one();
        if (is_object($auth)) {
            if ($auth->code === $auth_code) {
                $secret = RandomKey::generate(100);
                $created_at = $this->dic['datetimenow'];

                $session = ORM::for_table('sessions')->create();
                $session->session = $secret;
                $session->user_id = $auth->user_id;
                $session->created_at = $created_at;
                $session->checked_at = $created_at;

                if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
                    $session->ip = $_SERVER['REMOTE_ADDR'];
                }

                $session->save();

                if (is_object($session) AND isset($session->id)) {
                    $history_registration = ORM::for_table('history_registrations')->create();
                    $history_registration->device_uuid = (isset($device_info['uuid']) ? $device_info['uuid'] : 'unknown');
                    $history_registration->device_platform = (isset($device_info['platform']) ? $device_info['platform'] : 'unknown');
                    $history_registration->device_version = (isset($device_info['version']) ? $device_info['version'] : 'unknown');
                    $history_registration->device_manufacturer = (isset($device_info['manufacturer']) ? $device_info['manufacturer'] : 'unknown');
                    $history_registration->device_model = (isset($device_info['model']) ? $device_info['model'] : 'unknown');
                    $history_registration->created_at = $created_at;
                    $history_registration->save();

                    $cleaner->clear($auth_id);

                    return [
                        'data' => [
                            'session' => $secret,
                        ],
                    ];
                } else {
                    return [
                        'errors' => [
                            [
                                'status' => '500 Internal Server Error',
                                'code'   => 'danger',
                                'title'  => 'Произошла ошибка при аутентификации, попробуйте ещё раз',
                            ],
                        ],
                    ];
                }
            } else {
                if ($auth->attempts < 3) {
                    $auth->attempts++;
                    $auth->save();

                    return [
                        'errors' => [
                            [
                                'status' => '400 Bad Request',
                                'code'   => 'warning',
                                'title'  => 'Неверный код, попробуйте ещё раз',
                            ],
                        ],
                    ];
                } else {
                    $auth->delete();

                    return [
                        'errors' => [
                            [
                                'status' => '401 Unauthorized',
                                'code'   => 'danger',
                                'title'  => 'Попытка взлома, запросите код повторно',
                            ],
                        ],
                    ];
                }
            }
        } else {
            return [
                'errors' => [
                    [
                        'status' => '400 Bad Request',
                        'code'   => 'warning',
                        'title'  => 'Код отсутствует, перезапустите приложение',
                    ],
                ],
            ];
        }
    }
}
