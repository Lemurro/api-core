<?php

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Pimple\Container;

/**
 * Инициализация cron-задач
 */
class Console
{
    protected $dbal;

    public function __construct()
    {
        date_default_timezone_set('UTC');

        $this->dbal = DB::init();
    }

    /**
     * Получим DIC
     *
     * @return Container
     */
    public function getDIC()
    {
        $dic = DIC::init($this->dbal);

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
