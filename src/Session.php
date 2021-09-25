<?php

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Работа с сессиями
 */
class Session
{
    /**
     * Проверка валидности сессии
     *
     * @param string $session_id ИД сессии
     */
    public function check($session_id): array
    {
        if (empty($session_id)) {
            return Response::error401('Необходимо авторизоваться [#1]');
        }

        $session = ORM::for_table('sessions')
            ->where_equal('session', $session_id)
            ->find_one();

        if ($session === false) {
            return Response::error401('Необходимо авторизоваться [#2]');
        }

        if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            if ((empty($ip) || $session->ip !== (string) $ip)) {
                $session->delete();

                return Response::error401('Необходимо авторизоваться [#3]');
            }
        }

        $session->checked_at = Carbon::now('UTC')->toDateTimeString();
        $session->save();

        return $session->as_array();
    }

    /**
     * Очистка устаревших сессий
     */
    public function clearOlder(): void
    {
        $older_than = Carbon::now('UTC')->subDays(SettingsAuth::SESSIONS_OLDER_THAN)->toDateTimeString();

        ORM::for_table('sessions')
            ->where_lt('checked_at', $older_than)
            ->delete_many();
    }
}
