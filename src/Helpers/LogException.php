<?php

namespace Lemurro\Api\Core\Helpers;

use Exception;
use Monolog\Logger;

/**
 * Добавление в лог информации о пойманном исключении
 */
class LogException
{
    /**
     * Добавление в лог информации о пойманном исключении
     *
     * @param Logger $log Класс лога
     * @param Exception $e Информация об исключении
     */
    public static function write($log, $e): void
    {
        $log->error($e->getFile() . ' (line: ' . $e->getLine() . ') - ' . $e->getMessage(), $e->getTrace());
    }
}
