<?php

/**
 * Параметры справочников
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsGuides
{
    /**
     * Связка конечных точек маршрута справочников и их namespaces для запуска
     *
     * Пример:
     *   конечная точка: example (используется в пути: /guide/example)
     *        namespace: Example (полный путь до каталога классов: /app/Guide/Example/)
     */
    public static array $classes = [];
}
