<?php
/**
 * Инициализация Jobby
 *
 * @version 30.04.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Cron;

use Exception;
use Jobby\Jobby as JobbyJobby;
use Lemurro\Api\App\Configs\SettingsCron;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\Core\Helpers\Console;
use Lemurro\Api\Core\Helpers\File\FileOlderFiles;
use Lemurro\Api\Core\Helpers\File\FileOlderTokens;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;

/**
 * Class Jobby
 *
 * @package Lemurro\Api\Core
 */
class Jobby
{
    /**
     * @var Logger
     */
    public $log;

    /**
     * @var JobbyJobby
     */
    protected $jobby;

    /**
     * Jobby constructor.
     *
     * @version 29.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        date_default_timezone_set('UTC');

        $this->log = LoggerFactory::create('Jobby');

        $this->jobby = new JobbyJobby([
            'output'         => SettingsCron::LOG_FILE,
            'recipients'     => implode(',', SettingsCron::ERRORS_EMAILS),
            'mailer'         => 'smtp',
            'smtpHost'       => SettingsMail::SMTP_HOST,
            'smtpPort'       => SettingsMail::SMTP_PORT,
            'smtpUsername'   => SettingsMail::SMTP_USERNAME,
            'smtpPassword'   => SettingsMail::SMTP_PASSWORD,
            'smtpSecurity'   => SettingsMail::SMTP_SECURITY,
            'smtpSender'     => SettingsMail::APP_EMAIL,
            'smtpSenderName' => 'Jobby',
        ]);
    }

    /**
     * Инициализация
     *
     * @version 29.03.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function init()
    {
        $this->fileOlderTokens();
        $this->fileOlderFiles();

        return $this->jobby;
    }

    /**
     * Очистим устаревшие токены для скачивания
     *
     * @version 30.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function fileOlderTokens()
    {
        // Выполняем задачу каждые 5 минут
        try {
            $this->jobby->add(SettingsCron::NAME_PREFIX . 'FileOlderTokens', [
                'enabled'  => true,
                'schedule' => '*/5 * * * *', // Каждые 5 минут
                'closure'  => function () {
                    new Console();

                    (new FileOlderTokens)->clear();

                    return true;
                },
            ]);
        } catch (Exception $e) {
            $this->log->error($e->getFile() . '(' . $e->getLine() . '): ' . $e->getMessage());
        }
    }

    /**
     * Очистим устаревшие файлы во временном каталоге
     *
     * @version 29.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function fileOlderFiles()
    {
        // Выполняем задачу каждый день в 0:00 UTC
        try {
            $this->jobby->add(SettingsCron::NAME_PREFIX . 'FileOlderFiles', [
                'enabled'  => true,
                'schedule' => '0 0 * * *', // Каждый день в 0:00
                'closure'  => function () {
                    (new FileOlderFiles)->clear();

                    return true;
                },
            ]);
        } catch (Exception $e) {
            $this->log->error($e->getFile() . '(' . $e->getLine() . '): ' . $e->getMessage());
        }
    }
}
