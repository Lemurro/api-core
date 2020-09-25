<?php

/**
 * Очистка устаревших кодов аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsAuth;
use ORM;

/**
 * @package Lemurro\Api\Core\Auth\Code
 */
class Code
{
    /**
     * Выполним действие
     *
     * @param string $auth_id Идентификатор пользователя (номер телефона или электронная почта)
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function clear($auth_id = '')
    {
        $now = Carbon::now('UTC');

        ORM::for_table('auth_codes')
            ->where_lt('created_at', $now->subHours(SettingsAuth::$auth_codes_older_than))
            ->delete_many();

        if ($auth_id != '') {
            ORM::for_table('auth_codes')
                ->where_equal('auth_id', $auth_id)
                ->delete_many();
        }
    }
}
