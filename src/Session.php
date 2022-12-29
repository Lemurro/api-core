<?php

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Работа с сессиями
 */
class Session
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Проверка валидности сессии
     */
    public function check(string $session_id): array
    {
        if (empty($session_id)) {
            return Response::error401('Необходимо авторизоваться [#1]');
        }

        $session = $this->dbal->fetchAssociative('SELECT * FROM sessions WHERE session = ?', [$session_id]);
        if ($session === false) {
            return Response::error401('Необходимо авторизоваться [#2]');
        }

        if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            if ((empty($ip) || $session['ip'] !== (string) $ip)) {
                $this->dbal->delete('sessions', ['session' => $session_id]);

                return Response::error401('Необходимо авторизоваться [#3]');
            }
        }

        $this->dbal->update('sessions', [
            'checked_at' => Carbon::now('UTC')->toDateTimeString(),
        ], [
            'session' => $session_id,
        ]);

        return $session;
    }

    /**
     * Очистка устаревших сессий
     */
    public function clearOlder(): void
    {
        $this->dbal->executeStatement('DELETE FROM sessions WHERE checked_at <= ?', [
            Carbon::now('UTC')->subDays(SettingsAuth::SESSIONS_OLDER_THAN)->toDateTimeString(),
        ]);
    }
}
