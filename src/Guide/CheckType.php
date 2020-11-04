<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 04.11.2020
 */

namespace Lemurro\Api\Core\Guide;

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
     * @version 04.11.2020
     */
    protected function checkType(string $type): string
    {
        $guides_classes = $this->dic['config']['guides']['classes'];

        if (isset($guides_classes[$type])) {
            return $guides_classes[$type];
        }

        throw new ResponseException('Неизвестный справочник', 404);
    }
}
