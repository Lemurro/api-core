<?php
/**
 * Инициализация Jobby
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Cron;

use Jobby\Jobby as JobbyJobby;
use Lemurro\Api\App\Configs\SettingsCron;
use Lemurro\Api\App\Configs\SettingsMail;

/**
 * Class Jobby
 *
 * @package Lemurro\Api\Core
 */
class Jobby
{
    /**
     * Инициализация
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function init()
    {
        return new JobbyJobby([
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
}
