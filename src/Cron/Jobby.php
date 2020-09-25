<?php

/**
 * Инициализация Jobby
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Cron;

use Jobby\Jobby as JobbyJobby;
use Lemurro\Api\App\Configs\SettingsCron;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\Core\Helpers\Console;
use Lemurro\Api\Core\Helpers\File\FileOlderFiles;
use Lemurro\Api\Core\Helpers\File\FileOlderTokens;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;
use Throwable;

/**
 * Class Jobby
 *
 * @package Lemurro\Api\Core\Cron
 */
class Jobby
{
    public Logger $log;

    protected JobbyJobby $jobby;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function __construct()
    {
        date_default_timezone_set('UTC');

        $this->log = LoggerFactory::create('Jobby');

        $this->jobby = new JobbyJobby([
            'output'         => SettingsCron::$log_file,
            'recipients'     => implode(',', SettingsCron::$errors_emails),
            'mailer'         => 'smtp',
            'smtpHost'       => SettingsMail::$smtp_host,
            'smtpPort'       => SettingsMail::$smtp_port,
            'smtpUsername'   => SettingsMail::$smtp_username,
            'smtpPassword'   => SettingsMail::$smtp_password,
            'smtpSecurity'   => SettingsMail::$smtp_security,
            'smtpSender'     => SettingsMail::$app_email,
            'smtpSenderName' => 'Jobby',
        ]);
    }

    /**
     * Инициализация
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function init()
    {
        if (SettingsCron::$file_older_tokens_enabled) {
            $this->fileOlderTokens();
        }

        if (SettingsCron::$file_older_files_enabled) {
            $this->fileOlderFiles();
        }

        if (SettingsCron::$data_change_logs_rotator_enabled) {
            $this->dataChangeLogsRotator();
        }

        return $this->jobby;
    }

    /**
     * Очистим устаревшие токены для скачивания
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    protected function fileOlderTokens()
    {
        try {
            $this->jobby->add(SettingsCron::$name_prefix . 'FileOlderTokens', [
                'enabled'  => true,
                'schedule' => '*/5 * * * *', // Каждые 5 минут
                'closure'  => function () {
                    new Console();

                    (new FileOlderTokens())->clear();

                    return true;
                },
            ]);
        } catch (Throwable $t) {
            LogException::write($this->log, $t);
        }
    }

    /**
     * Очистим устаревшие файлы во временном каталоге
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    protected function fileOlderFiles()
    {
        try {
            $this->jobby->add(SettingsCron::$name_prefix . 'FileOlderFiles', [
                'enabled'  => true,
                'schedule' => '0 0 * * *', // Каждый день в 0:00 UTC
                'closure'  => function () {
                    (new FileOlderFiles())->clear();

                    return true;
                },
            ]);
        } catch (Throwable $t) {
            LogException::write($this->log, $t);
        }
    }

    /**
     * Ротация таблицы data_change_logs
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    protected function dataChangeLogsRotator()
    {
        try {
            $this->jobby->add(SettingsCron::$name_prefix . 'DataChangeLogsRotator', [
                'enabled'  => true,
                'schedule' => '0 0 1 1 *', // Каждый год 1 января в 0:00
                'closure'  => function () {
                    $cron = new Console();
                    $dic = $cron->getDIC();

                    (new DataChangeLogsRotator($dic))->execute();

                    return true;
                },
            ]);
        } catch (Throwable $t) {
            LogException::write($this->log, $t);
        }
    }
}
