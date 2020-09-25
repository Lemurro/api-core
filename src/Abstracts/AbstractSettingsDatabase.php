<?php

/**
 * Параметры БД
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

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
     * Сервер
     */
    public static string $host = '127.0.0.1';

    /**
     * Порт
     */
    public static int $port = 3306;

    /**
     * Имя БД
     */
    public static string $dbname = 'lemurro';

    /**
     * Пользователь
     */
    public static string $username = 'root';

    /**
     * Пароль
     */
    public static string $password = '';

    /**
     * Сбор выполняемых запросов:
     * ORM::get_last_query() (Возвращает строку)
     * ORM::get_query_log() (Возвращает массив)
     */
    public static bool $logging = true;
}
