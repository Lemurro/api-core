<?php

/**
 * Проверка валидности сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 27.11.2020
 */

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core
 */
class Session
{
    private array $config_auth;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_auth)
    {
        $this->config_auth = $config_auth;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 27.11.2020
     */
    public function check(string $session_id): array
    {
        if (empty($session_id)) {
            return Response::error401('Необходимо авторизоваться [#1]');
        }

        $session = DB::table('sessions')
            ->where('session', '=', $session_id)
            ->first();

        if ($session === null) {
            return Response::error401('Необходимо авторизоваться [#3]');
        }

        if ($this->config_auth['sessions_binding_to_ip'] && $session->ip !== $_SERVER['REMOTE_ADDR']) {
            DB::table('sessions')
                ->where('session', '=', $session_id)
                ->delete();

            return Response::error401('Необходимо авторизоваться [#2]');
        }

        $session->checked_at = Carbon::now('UTC')->toDateTimeString();

        DB::table('sessions')
            ->where('session', '=', $session_id)
            ->update([
                'checked_at' => $session->checked_at,
            ]);

        return (array) $session;
    }
}
