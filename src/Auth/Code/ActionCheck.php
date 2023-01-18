<?php

namespace Lemurro\Api\Core\Auth\Code;

use Doctrine\DBAL\Connection;
use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Проверка кода аутентификации
 */
class ActionCheck extends Action
{
    /**
     * Проверка кода аутентификации
     *
     * @param string $auth_id     Номер телефона или электронная почта
     * @param string $auth_code   Код из СМС или письма
     * @param array  $device_info Информация об устройстве
     * @param array  $geoip       Информация о геолокации
     *
     * @return array
     */
    public function run($auth_id, $auth_code, $device_info, $geoip): array
    {
        $cleaner = new Code($this->dbal);

        $cleaner->clear();

        $auth = $this->dbal->fetchAssociative('SELECT * FROM auth_codes WHERE auth_id = ?', [$auth_id]);
        if ($auth === false) {
            return Response::error400('Код отсутствует, перезапустите приложение');
        }

        if ($auth['code'] === $auth_code) {
            $session = $this->dbal->transactional(function (Connection $dbal) use ($auth, $device_info, $geoip, $cleaner, $auth_id): string {
                $ip = null;
                /** @psalm-suppress TypeDoesNotContainType */
                if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
                    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                }

                $secret = RandomKey::generate(100);
                $created_at = $this->dic['datetimenow'];

                $dbal->insert('sessions', [
                    'session' => $secret,
                    'ip' => $ip,
                    'user_id' => $auth['user_id'],
                    'device_info' => json_encode($device_info, JSON_UNESCAPED_UNICODE),
                    'geoip' => json_encode($geoip, JSON_UNESCAPED_UNICODE),
                    'created_at' => $created_at,
                    'checked_at' => $created_at,
                ]);

                $dbal->insert('history_registrations', [
                    'device_uuid' => $device_info['uuid'] ?? 'unknown',
                    'device_platform' => $device_info['platform'] ?? 'unknown',
                    'device_version' => $device_info['version'] ?? 'unknown',
                    'device_manufacturer' => $device_info['manufacturer'] ?? 'unknown',
                    'device_model' => $device_info['model'] ?? 'unknown',
                    'created_at' => $created_at,
                ]);

                $cleaner->clear($auth_id);

                return $secret;
            });

            return Response::data([
                'session' => $session,
            ]);
        }

        if ($auth['attempts'] < 3) {
            $this->dbal->update('auth_codes', [
                'attempts' => $auth['attempts'] + 1,
            ], [
                'auth_id' => $auth_id
            ]);

            return Response::error400('Неверный код, попробуйте ещё раз');
        }

        $this->dbal->delete('auth_codes', ['auth_id' => $auth_id]);

        return Response::error401('Попытка взлома, запросите код повторно');
    }
}
