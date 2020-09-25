<?php

/**
 * Инициализация Dependency Injection Container
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
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
     * @version 25.09.2020
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
            $phpmailer->From = SettingsMail::$app_email;
            $phpmailer->FromName = iconv('utf-8', 'windows-1251', SettingsGeneral::$app_name);

            if (SettingsMail::$smtp) {
                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = SettingsMail::$smtp_security;
                $phpmailer->Host = SettingsMail::$smtp_host;
                $phpmailer->Port = SettingsMail::$smtp_port;
                $phpmailer->Username = SettingsMail::$smtp_username;
                $phpmailer->Password = SettingsMail::$smtp_password;
            }

            return $phpmailer;
        };

        if (SettingsMail::$reserve) {
            $dic['phpmailer_reserve'] = function () {
                $phpmailer = new PHPMailer();
                $phpmailer->isHTML(true);
                $phpmailer->CharSet = 'windows-1251';
                $phpmailer->From = SettingsMail::$reserve_app_email;
                $phpmailer->FromName = iconv('utf-8', 'windows-1251', SettingsGeneral::$app_name);

                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = SettingsMail::$reserve_smtp_security;
                $phpmailer->Host = SettingsMail::$reserve_smtp_host;
                $phpmailer->Port = SettingsMail::$reserve_smtp_port;
                $phpmailer->Username = SettingsMail::$reserve_smtp_username;
                $phpmailer->Password = SettingsMail::$reserve_smtp_password;

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
