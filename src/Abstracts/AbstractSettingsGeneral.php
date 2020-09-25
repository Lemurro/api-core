<?php

/**
 * Основные параметры
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsGeneral
{
    /**
     * Имя проекта
     */
    public static string $app_name = 'Lemurro';

    /**
     * Это боевой сервер если стоит значение true
     *
     * @deprecated 2.0
     */
    public static bool $production = false;

    /**
     * Вид сервера
     */
    public static string $server_type = self::$server_type_dev;

    /**
     * Вид сервера: разработчика
     */
    public static string $server_type_dev = 'dev';

    /**
     * Вид сервера: тестовый
     */
    public static string $server_type_test = 'test';

    /**
     * Вид сервера: боевой
     */
    public static string $server_type_prod = 'prod';
}
