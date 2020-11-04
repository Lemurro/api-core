<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;
use Throwable;

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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($auth_id, $auth_code, $device_info, $geoip): array
    {
        $cleaner = new Code($this->dic['config']['auth']['auth_codes_older_than_hours']);

        $cleaner->clear();

        $auth = DB::table('auth_codes')
            ->where('auth_id', '=', $auth_id)
            ->first();

        if ($auth === null) {
            return Response::error400('Код отсутствует, перезапустите приложение');
        }

        if ($auth->code === $auth_code) {
            $ip = '';

            if (isset($geoip['ip'])) {
                $ip = $geoip['ip'];
            }

            if ($auth->ip !== $ip) {
                return Response::error401('Попытка взлома, запросите код повторно');
            }

            $secret = RandomKey::generate(100);
            $created_at = $this->datetimenow;
            $ip = $this->dic['config']['auth']['sessions_binding_to_ip'] ? $_SERVER['REMOTE_ADDR'] : null;

            try {
                DB::beginTransaction();

                DB::table('sessions')->insert([
                    'session' => $secret,
                    'ip' => $ip,
                    'user_id' => $auth->user_id,
                    'device_info' => json_encode($device_info, JSON_UNESCAPED_UNICODE),
                    'geoip' => json_encode($geoip, JSON_UNESCAPED_UNICODE),
                    'created_at' => $created_at,
                    'checked_at' => $created_at,
                ]);

                DB::table('history_registrations')->insert([
                    'device_uuid' => $device_info['uuid'] ?? 'unknown',
                    'device_platform' => $device_info['platform'] ?? 'unknown',
                    'device_version' => $device_info['version'] ?? 'unknown',
                    'device_manufacturer' => $device_info['manufacturer'] ?? 'unknown',
                    'device_model' => $device_info['model'] ?? 'unknown',
                    'created_at' => $created_at,
                ]);

                $cleaner->clear($auth_id);

                DB::commit();

                return Response::data([
                    'session' => $secret,
                ]);
            } catch (Throwable $th) {
                DB::rollBack();

                LogException::write($this->dic['log'], $th);

                return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
            }
        }

        if ($auth->attempts < 3) {
            DB::table('auth_codes')
                ->where('id', '=', $auth->id)
                ->increment('attempts');

            return Response::error400('Неверный код, попробуйте ещё раз');
        }

        DB::table('auth_codes')->delete($auth->id);

        return Response::error401('Попытка взлома, запросите код повторно');
    }
}
