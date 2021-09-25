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
            return Response::error401('Необходимо авторизоваться');
        }

        $session = ORM::for_table('sessions')
            ->where_equal('session', $session_id)
            ->find_one();
        if (is_object($session) && $session->session == $session_id) {
            if (SettingsAuth::SESSIONS_BINDING_TO_IP && $session->ip !== $_SERVER['REMOTE_ADDR']) {
                $session->delete();

                return Response::error401('Необходимо авторизоваться');
            }

            $session->checked_at = $checked_at;
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
