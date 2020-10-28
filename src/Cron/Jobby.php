<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 28.10.2020
 */

namespace Lemurro\Api\Core\Cron;

use Jobby\Jobby as JobbyJobby;
use Lemurro\Api\Core\Helpers\Console;
use Lemurro\Api\Core\Helpers\Database;
use Lemurro\Api\Core\Helpers\File\FileOlderFiles;
use Lemurro\Api\Core\Helpers\File\FileOlderTokens;
use Lemurro\Api\Core\Helpers\LogException;
use Monolog\Logger;
use Pimple\Container;
use Throwable;

/**
 * @package Lemurro\Api\Core\Cron
 */
class Jobby
{
    public Container $dic;
    public Logger $log;
    protected JobbyJobby $jobby;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(string $path_root)
    {
        $this->dic = (new Console())->getDIC($path_root);
        $this->log = $this->dic['logfactory']->create('Jobby');

        $this->jobby = new JobbyJobby([
            'output'         => $this->dic['config']['cron']['log_file'],
            'recipients'     => implode(',', $this->dic['config']['cron']['errors_emails']),
            'mailer'         => 'smtp',
            'smtpHost'       => $this->dic['config']['mail']['smtp_host'],
            'smtpPort'       => $this->dic['config']['mail']['smtp_port'],
            'smtpUsername'   => $this->dic['config']['mail']['smtp_username'],
            'smtpPassword'   => $this->dic['config']['mail']['smtp_password'],
            'smtpSecurity'   => $this->dic['config']['mail']['smtp_security'],
            'smtpSender'     => $this->dic['config']['mail']['app_email'],
            'smtpSenderName' => 'Jobby',
        ]);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function init()
    {
        if ($this->dic['config']['cron']['file_older_tokens_enabled']) {
            $this->fileOlderTokens();
        }

        if ($this->dic['config']['cron']['file_older_files_enabled']) {
            $this->fileOlderFiles();
        }

        if ($this->dic['config']['cron']['data_change_logs_rotator_enabled']) {
            $this->dataChangeLogsRotator();
        }

        return $this->jobby;
    }

    /**
     * Очистим устаревшие токены для скачивания
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 28.10.2020
     */
    protected function fileOlderTokens()
    {
        try {
            $this->jobby->add($this->dic['config']['cron']['name_prefix'] . 'FileOlderTokens', [
                'enabled'  => true,
                'schedule' => '*/5 * * * *', // Каждые 5 минут
                'closure'  => function () {
                    Database::init($this->dic['config']['database']);

                    (new FileOlderTokens($this->dic['config']['file']))->clear();

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
     * @version 14.10.2020
     */
    protected function fileOlderFiles()
    {
        try {
            $this->jobby->add($this->dic['config']['cron']['name_prefix'] . 'FileOlderFiles', [
                'enabled'  => true,
                'schedule' => '0 0 * * *', // Каждый день в 0:00 UTC
                'closure'  => function () {
                    (new FileOlderFiles($this->dic['config']['file']))->clear();

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
     * @version 28.10.2020
     */
    protected function dataChangeLogsRotator()
    {
        try {
            $this->jobby->add($this->dic['config']['cron']['name_prefix'] . 'DataChangeLogsRotator', [
                'enabled'  => true,
                'schedule' => '0 0 1 1 *', // Каждый год 1 января в 0:00
                'closure'  => function () {
                    Database::init($this->dic['config']['database']);

                    (new DataChangeLogsRotator($this->dic))->execute();

                    return true;
                },
            ]);
        } catch (Throwable $t) {
            LogException::write($this->log, $t);
        }
    }
}
