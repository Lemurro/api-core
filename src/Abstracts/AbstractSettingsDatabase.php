<?php

/**
 * Параметры БД
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 01.10.2020
 */

namespace Lemurro\Api\Core\Abstracts;

use PDO;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsDatabase
{
    /**
     * Нужно ли подключаться к БД
     */
    public static bool $need_connect = true;

    /**
     * Имя источника данных
     */
    public static string $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=lemurro';

    /**
     * Пользователь
     */
    public static string $username = 'root';

    /**
     * Пароль
     */
    public static string $password = '';

    /**
     * Опции драйвера
     */
    public static array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    ];

    /**
     * Сбор выполняемых запросов:
     * ORM::get_last_query() (Возвращает строку)
     * ORM::get_query_log() (Возвращает массив)
     */
    public static bool $logging = true;
}
