<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * @package Lemurro\Api\Core\Auth\Code
 */
class ActionCheck extends Action
{
    /**
     * @param string $auth_id     Номер телефона или электронная почта
     * @param string $auth_code   Код из СМС или письма
     * @param array  $device_info Информация об устройстве
     * @param array  $geoip       Информация о геолокации
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run($auth_id, $auth_code, $device_info, $geoip): array
    {
        $cleaner = new Code($this->dic['config']['auth']['auth_codes_older_than_hours']);

        $cleaner->clear();

        $auth = ORM::for_table('auth_codes')
            ->where_equal('auth_id', $auth_id)
            ->find_one();
        if (is_object($auth)) {
            if ($auth->code === $auth_code) {
                $secret = RandomKey::generate(100);
                $created_at = $this->datetimenow;

                $session = ORM::for_table('sessions')->create();
                $session->session = $secret;
                $session->user_id = $auth->user_id;
                $session->device_info = json_encode($device_info, JSON_UNESCAPED_UNICODE);
                $session->geoip = json_encode($geoip, JSON_UNESCAPED_UNICODE);
                $session->created_at = $created_at;
                $session->checked_at = $created_at;

                if ($this->dic['config']['auth']['sessions_binding_to_ip']) {
                    $session->ip = $_SERVER['REMOTE_ADDR'];
                }

                $session->save();

                if (is_object($session) && isset($session->id)) {
                    $history_registration = ORM::for_table('history_registrations')->create();
                    $history_registration->device_uuid = ($device_info['uuid'] ?? 'unknown');
                    $history_registration->device_platform = ($device_info['platform'] ?? 'unknown');
                    $history_registration->device_version = ($device_info['version'] ?? 'unknown');
                    $history_registration->device_manufacturer = ($device_info['manufacturer'] ?? 'unknown');
                    $history_registration->device_model = ($device_info['model'] ?? 'unknown');
                    $history_registration->created_at = $created_at;
                    $history_registration->save();

                    $cleaner->clear($auth_id);

                    return Response::data([
                        'session' => $secret,
                    ]);
                }

                return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
            }

            if ($auth->attempts < 3) {
                $auth->attempts++;
                $auth->save();

                return Response::error400('Неверный код, попробуйте ещё раз');
            }

            $auth->delete();

            return Response::error401('Попытка взлома, запросите код повторно');
        }

        return Response::error400('Код отсутствует, перезапустите приложение');
    }
}
