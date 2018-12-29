<?php
/**
 * Инициализация cron-задач
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Cron;

use Lemurro\Api\Core\DB;
use Lemurro\Api\Core\DIC;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use ORM;
use Pimple\Container;

/**
 * Class Cron
 *
 * @package Lemurro\Cron
 */
class Cron
{
    /**
     * Cron constructor.
     */
    public function __construct()
    {
        DB::init();
    }

    /**
     * Получим DIC
     *
     * @return Container
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getDIC()
    {
        $dic = DIC::init();

        $dic['utc_offset'] = 0;

        $dic['user'] = function ($c) {
            $first_admin = ORM::for_table('info_users')
                ->select('user_id')
                ->where_any_is([
                    ['roles' => '{"admin":"true"}'],
                    ['roles' => '{"admin":true}'],
                ])
                ->order_by_asc('user_id')
                ->limit(1)
                ->find_one();
            if (is_object($first_admin)) {
                $user_id = $first_admin->user_id;
            } else {
                $user_id = 1;
            }

            $user_info = (new GetUser($c))->run($user_id);
            if (isset($user_info['data'])) {
                $user_info['data']['admin'] = (isset($user_info['data']['roles']['admin']) ? true : false);

                return $user_info['data'];
            } else {
                return [];
            }
        };

        return $dic;
    }
}
