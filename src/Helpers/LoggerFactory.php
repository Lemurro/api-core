<?php

/**
 * Фабрика создания логгеров Monolog
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 27.05.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Helpers\File\FileNameCleaner;
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 27.05.2020
     */
    public static function create($name)
    {
        $name = FileNameCleaner::clean((string) $name);
        $name = trim($name);

        if (empty($name)) {
            $name = 'Main';
        }

        $logger = new Logger($name);
        $filename = SettingsPath::LOGS . mb_strtolower($name, 'UTF-8') . '.log';
        $handler = new RotatingFileHandler($filename);

        $handler->setFilenameFormat('{date}-{filename}', 'Y/m/d');

        $logger->pushHandler($handler);

        return $logger;
    }
}
