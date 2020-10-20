<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
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
     * @version 14.10.2020
     */
    protected function checkType(string $type): array
    {
        if (isset($this->dic['config']['guides']['classes'][$type])) {
            return $this->dic['config']['guides']['classes'][$type];
        }

        throw new ResponseException('Неизвестный справочник', 404);
    }
}
