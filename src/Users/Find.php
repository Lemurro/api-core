<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;

/**
 * @package Lemurro\Api\Core\Users
 */
class Find
{
    /**
     * @param string $auth_id Номер телефона или электронная почта
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($auth_id): array
    {
        $user = DB::table('users')
            ->where('auth_id', '=', $auth_id)
            ->whereNull('deleted_at')
            ->first();

        if ($user === null) {
            return [];
        }

        if (mb_strtolower($user->auth_id, 'UTF-8') == mb_strtolower($auth_id, 'UTF-8')) {
            return (array) $user;
        }
    }
}
