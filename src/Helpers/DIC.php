<?php
/**
 * Инициализация Dependency Injection Container
 *
 * @version 16.07.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\Core\Checker\Checker;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;

/**
 * Class DIC
 *
 * @package Lemurro\Api\Core\Helpers
 */
class DIC
{
    /**
     * Инициализация
     *
     * @return Container
     *
     * @version 16.07.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function init()
    {
        $dic = new Container();

        $dic['utc_offset'] = 0;

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

        if (SettingsMail::RESERVE) {
            $dic['phpmailer_reserve'] = function () {
                $phpmailer = new PHPMailer();
                $phpmailer->isHTML(true);
                $phpmailer->CharSet = 'windows-1251';
                $phpmailer->From = SettingsMail::APP_EMAIL;
                $phpmailer->FromName = iconv('utf-8', 'windows-1251', SettingsGeneral::APP_NAME);

                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = SettingsMail::RESERVE_SMTP_SECURITY;
                $phpmailer->Host = SettingsMail::RESERVE_SMTP_HOST;
                $phpmailer->Port = SettingsMail::RESERVE_SMTP_PORT;
                $phpmailer->Username = SettingsMail::RESERVE_SMTP_USERNAME;
                $phpmailer->Password = SettingsMail::RESERVE_SMTP_PASSWORD;

                return $phpmailer;
            };
        }

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
            return LoggerFactory::create('Main');
        };

        $dic['checker'] = function ($c) {
            return new Checker($c);
        };

        return $dic;
    }
}
