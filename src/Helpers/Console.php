<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Pimple\Container;

/**
 * @package Lemurro\Helpers
 */
class Console
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function getDIC(string $path_root): Container
    {
        date_default_timezone_set('UTC');

        $dic = DIC::init($path_root);

        $dic['user'] = function ($c) {
            $user_info = (new GetUser($c))->run(1);
            if (isset($user_info['data'])) {
                $user_info['data']['admin'] = true;

                return $user_info['data'];
            }

            return [];
        };

        (new AppDIC())->run($dic);

        return $dic;
    }
}
