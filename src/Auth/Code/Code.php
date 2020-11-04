<?php

/**
 * Очистка устаревших кодов аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @package Lemurro\Api\Core\Auth\Code
 */
class Code
{
    private int $auth_codes_older_than_hours;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(int $auth_codes_older_than_hours)
    {
        $this->auth_codes_older_than_hours = $auth_codes_older_than_hours;
    }

    /**
     * @param string $auth_id Идентификатор пользователя (номер телефона или электронная почта)
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function clear($auth_id = '')
    {
        $now = Carbon::now('UTC');
        $older_than = $now->subHours($this->auth_codes_older_than_hours)->toDateTimeString();

        DB::table('auth_codes')
            ->where('created_at', '<', $older_than)
            ->delete();

        if ($auth_id != '') {
            DB::table('auth_codes')
                ->where('auth_id', '=', $auth_id)
                ->delete();
        }
    }
}
