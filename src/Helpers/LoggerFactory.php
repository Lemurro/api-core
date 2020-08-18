<?php

/**
 * Фабрика создания логгеров Monolog
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 18.08.2020
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 18.08.2020
     */
    public static function create(string $file_name = 'Main', string $channel_name = 'Main'): Logger
    {
        $file_name = FileNameCleaner::clean($file_name);
        $file_name = trim($file_name);

        if (empty($file_name)) {
            $file_name = 'Main';
        }

        if (empty($channel_name)) {
            $channel_name = 'Main';
        }

        $logger = new Logger($channel_name);
        $file_path = SettingsPath::LOGS . mb_strtolower($file_name, 'UTF-8') . '.log';
        $handler = new RotatingFileHandler($file_path);

        $handler->setFilenameFormat('{date}-{filename}', 'Y/m/d');

        $logger->pushHandler($handler);

        return $logger;
    }
}
