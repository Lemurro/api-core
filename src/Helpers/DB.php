<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\SettingsDatabase;
use ORM;
use PDO;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class DB
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public static function init()
    {
        if (SettingsDatabase::$need_connect) {
            $connection_string = 'mysql:host=' . SettingsDatabase::$host . ';port=' . SettingsDatabase::$port . ';dbname=' . SettingsDatabase::$dbname;

            ORM::configure('connection_string', $connection_string);
            ORM::configure('username', SettingsDatabase::$username);
            ORM::configure('password', SettingsDatabase::$password);
            ORM::configure('logging', SettingsDatabase::$logging);
            ORM::configure('driver_options', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]);
        }
    }
}
