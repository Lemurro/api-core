<?php
/**
 * Проверка валидности сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 15.10.2019
 */

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class Session
 *
 * @package Lemurro\Api\Core
 */
class Session
{
    /**
     * Проверим сессию на валидность
     *
     * @param string $session_id ИД сессии
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.10.2019
     */
    public function check($session_id)
    {
        $now = Carbon::now('UTC');
        $checked_at = $now->toDateTimeString();

        ORM::for_table('sessions')
            ->where_lt('checked_at', $now->subDays(SettingsAuth::SESSIONS_OLDER_THAN))
            ->delete_many();

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
        } else {
            return Response::error401('Необходимо авторизоваться');
        }
    }
}
