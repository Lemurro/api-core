<?php
/**
 * Очистка устаревших кодов аутентификации
 *
 * @version 13.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Auth\Code;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsAuth;
use ORM;

/**
 * Class Code
 *
 * @package Lemurro\Api\Core\Auth\Code
 */
class Code
{
    /**
     * Выполним действие
     *
     * @param string $auth_id Идентификатор пользователя (номер телефона или электронная почта)
     *
     * @version 13.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function clear($auth_id = '')
    {
        $now = Carbon::now('UTC');
        $older_than = $now->subHours(SettingsAuth::AUTH_CODES_OLDER_THAN)->toDateTimeString();

        ORM::for_table('auth_codes')
            ->where_lt('created_at', $older_than)
            ->delete_many();

        if ($auth_id != '') {
            ORM::for_table('auth_codes')
                ->where_equal('auth_id', $auth_id)
                ->delete_many();
        }
    }
}
