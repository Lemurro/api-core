<?php
/**
 * Инициализация ORM для запросов к БД
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Lemurro\Api\App\Configs\SettingsDatabase;
use ORM;
use PDO;

/**
 * Class DB
 *
 * @package Lemurro\Api\Core
 */
class DB
{
    /**
     * Инициализация
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function init()
    {
        if (SettingsDatabase::NEED_CONNECT) {
            $connection_string = 'mysql:host=' . SettingsDatabase::HOST . ';port=' . SettingsDatabase::PORT . ';dbname=' . SettingsDatabase::DBNAME;

            ORM::configure('connection_string', $connection_string);
            ORM::configure('username', SettingsDatabase::USERNAME);
            ORM::configure('password', SettingsDatabase::PASSWORD);
            ORM::configure('logging', SettingsDatabase::LOGGING);
            ORM::configure('driver_options', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]);
        }
    }
}
