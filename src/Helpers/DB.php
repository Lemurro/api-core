<?php

namespace Lemurro\Api\Core\Helpers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Lemurro\Api\App\Configs\SettingsDatabase;
use ORM;
use PDO;

/**
 * Инициализация ORM для запросов к БД
 */
class DB
{
    /**
     * Инициализация
     */
    static function init(): ?Connection
    {
        if (SettingsDatabase::NEED_CONNECT) {
            $conn = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'user' => SettingsDatabase::USERNAME,
                'password' => SettingsDatabase::PASSWORD,
                'host' => SettingsDatabase::HOST,
                'port' => SettingsDatabase::PORT,
                'dbname' => SettingsDatabase::DBNAME,
                'charset' => 'utf8',
                'driverOptions' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                ],
            ]);

            ORM::set_db($conn->getNativeConnection());

            return $conn;
        }

        return null;
    }
}
