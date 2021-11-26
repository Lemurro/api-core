<?php
/**
 * Добавление в лог информации о пойманном исключении
 *
 * @version 28.05.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

use Exception;
use Monolog\Logger;

/**
 * Class LogException
 *
 * @package Lemurro\Api\Core\Helpers
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
    static function write($log, $e)
    {
        return $log->error($e->getFile() . ' (line: ' . $e->getLine() . ') - ' . $e->getMessage(), $e->getTrace());
    }
}
