<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Facade;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class Database
{
    private Capsule $capsule;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function __construct()
    {
        $this->capsule = new Capsule();

        // Лайфхак, чтобы работал фасад DB
        $app = new Container();
        $app->singleton('app', 'Illuminate\Container\Container');
        $app->instance('db', $this->capsule->getDatabaseManager());
        Facade::setFacadeApplication($app);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function addConnection(array $db_config, string $name = 'default'): self
    {
        $this->capsule->addConnection($db_config, $name);

        return $this;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function connect(): void
    {
        $this->capsule->setAsGlobal();
    }
}
