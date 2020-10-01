<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 01.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\SettingsDatabase;
use ORM;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class DB
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 01.10.2020
     */
    public static function init()
    {
        if (SettingsDatabase::$need_connect) {
            ORM::configure('connection_string', SettingsDatabase::$dsn);
            ORM::configure('username', SettingsDatabase::$username);
            ORM::configure('password', SettingsDatabase::$password);
            ORM::configure('logging', SettingsDatabase::$logging);
            ORM::configure('driver_options', SettingsDatabase::$options);
        }
    }
}
