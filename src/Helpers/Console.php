<?php
/**
 * Инициализация cron-задач
 *
 * @version 30.04.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Pimple\Container;

/**
 * Class Console
 *
 * @package Lemurro\Helpers
 */
class Console
{
    /**
     * Console constructor.
     *
     * @version 30.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        date_default_timezone_set('UTC');

        DB::init();
    }

    /**
     * Получим DIC
     *
     * @return Container
     *
     * @version 30.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getDIC()
    {
        $dic = DIC::init();

        $dic['user'] = function ($c) {
            $user_info = (new GetUser($c))->run(1);
            if (isset($user_info['data'])) {
                $user_info['data']['admin'] = true;

                return $user_info['data'];
            } else {
                return [];
            }
        };

        (new AppDIC())->run($dic);

        return $dic;
    }
}
