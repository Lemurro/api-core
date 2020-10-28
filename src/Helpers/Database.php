<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 28.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use ORM;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class Database
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 28.10.2020
     */
    public static function init(array $db_config)
    {
        ORM::configure('connection_string', $db_config['dsn']);
        ORM::configure('username', $db_config['username']);
        ORM::configure('password', $db_config['password']);
        ORM::configure('logging', $db_config['logging']);
        ORM::configure('driver_options', $db_config['options']);
    }
}
