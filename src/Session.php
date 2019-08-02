<?php
/**
 * Проверка валидности сессии
 *
 * @version 02.08.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * @version 02.08.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
        if (is_object($session)) {
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
