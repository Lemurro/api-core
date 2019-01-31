<?php
/**
 * Фабрика создания логгеров Monolog
 *
 * @version 31.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\SettingsPath;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Class LoggerFactory
 *
 * @package Lemurro\Api\Core\Helpers
 */
class LoggerFactory
{
    /**
     * Создание логгера
     *
     * @param string $name Имя логгера (используется также для имени файла)
     *
     * @return Logger
     *
     * @version 31.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function create($name)
    {
        $logger = new Logger($name);
        $filename = SettingsPath::LOGS . mb_strtolower($name, 'UTF-8') . '.log';
        $handler = new RotatingFileHandler($filename);

        $logger->pushHandler($handler);

        return $logger;
    }
}
