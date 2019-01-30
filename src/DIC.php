<?php
/**
 * Инициализация Dependency Injection Container
 *
 * @version 30.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Checker\Checker;
use Lemurro\Api\Core\DataChangeLog\DataChangeLog;
use Lemurro\Api\Core\SMS\SMS;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;

/**
 * Class DIC
 *
 * @package Lemurro\Api\Core
 */
class DIC
{
    /**
     * Инициализация
     *
     * @return Container
     *
     * @version 30.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function init()
    {
        $dic = new Container();

        $dic['datetimenow'] = function () {
            $now = Carbon::now('UTC');

            return $now->toDateTimeString();
        };

        $dic['phpmailer'] = function () {
            $phpmailer = new PHPMailer();
            $phpmailer->isHTML(true);
            $phpmailer->CharSet = 'windows-1251';
            $phpmailer->From = SettingsMail::APP_EMAIL;
            $phpmailer->FromName = iconv('utf-8', 'windows-1251', SettingsGeneral::APP_NAME);

            if (SettingsMail::SMTP) {
                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = SettingsMail::SMTP_SECURITY;
                $phpmailer->Host = SettingsMail::SMTP_HOST;
                $phpmailer->Port = SettingsMail::SMTP_PORT;
                $phpmailer->Username = SettingsMail::SMTP_USERNAME;
                $phpmailer->Password = SettingsMail::SMTP_PASSWORD;
            }

            return $phpmailer;
        };

        $dic['mailer'] = function ($c) {
            return new Mailer($c);
        };

        $dic['sms'] = function () {
            return new SMS();
        };

        $dic['datachangelog'] = function ($c) {
            return new DataChangeLog($c);
        };

        $dic['log'] = function () {
            $log = new Logger('MainLog');
            $handler = new RotatingFileHandler(SettingsPath::LOGS . 'main.log');

            $log->pushHandler($handler);

            return $log;
        };

        $dic['checker'] = function ($c) {
            return new Checker($c);
        };

        return $dic;
    }
}
