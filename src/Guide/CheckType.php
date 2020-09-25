<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Exception\ResponseException;

/**
 * @package Lemurro\Api\Core\Guide
 */
trait CheckType
{
    /**
     * @throws ResponseException
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    protected function checkType(string $type): array
    {
        if (isset(SettingsGuides::$classes[$type])) {
            return SettingsGuides::$classes[$type];
        }

        throw new ResponseException('Неизвестный справочник', 404);
    }
}
