<?php

/**
 * Настройка путей
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 07.10.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsPath
{
    /**
     * Полный путь до корня (с конечной "/")
     */
    public static string $root = __DIR__ . '/../../../../../';

    /**
     * Полный путь до каталога логов (с конечной "/")
     */
    public static string $logs = __DIR__ . '/../../../../../logs/';
}
