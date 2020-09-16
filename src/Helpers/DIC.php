<?php

/**
 * Инициализация Dependency Injection Container
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class DIC
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
     */
    public static function init(): Container
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
                $phpmailer->From = SettingsMail::RESERVE_APP_EMAIL;
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

        return $dic;
    }
}
