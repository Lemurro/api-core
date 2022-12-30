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
     * Выполним действие
     *
     * @param Logger    $log Класс лога
     * @param Exception $e   Информация об исключении
     *
     * @return boolean
     *
     * @version 28.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public static function write($log, $e)
    {
        return $log->error($e->getFile() . ' (line: ' . $e->getLine() . ') - ' . $e->getMessage(), $e->getTrace());
    }
}
