<?php

namespace Lemurro\Api\Core\Helpers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Lemurro\Api\App\Configs\SettingsDatabase;
use PDO;

/**
 * Инициализация ORM для запросов к БД
 */
class DB
{
    /**
     * Инициализация
     */
    public static function init(): ?Connection
    {
        /** @psalm-suppress RedundantCondition */
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
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => true,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                ],
            ]);

            return $conn;
        }

        return null;
    }
}
