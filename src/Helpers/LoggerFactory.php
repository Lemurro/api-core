<?php

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Helpers\File\FileNameCleaner;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Фабрика создания логгеров Monolog
 */
class LoggerFactory
{
    /**
     * Создание логгера
     *
     * @param string $name Имя логгера (используется для имени файла, если не указан используется 'main')
     * @param string $channel_name Канал логгера (если не указан используется 'Main')
     *
     * @return Logger
     */
    public static function create($name, $channel_name = null)
    {
        $name = FileNameCleaner::clean((string) $name);
        $name = trim($name);

        if (empty($name)) {
            $name = 'main';
        }

        if (empty($channel_name)) {
            $channel_name = 'Main';
        }

        $logger = new Logger($channel_name);
        $filename = SettingsPath::LOGS . mb_strtolower($name, 'UTF-8') . '.log';
        $handler = new RotatingFileHandler($filename);

        $handler->setFilenameFormat('{date}-{filename}', 'Y/m/d');

        $logger->pushHandler($handler);

        return $logger;
    }
}
