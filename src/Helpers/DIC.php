<?php

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\Core\Checker\Checker;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;

/**
 * Инициализация Dependency Injection Container
 */
class DIC
{
    /**
     * Инициализация Dependency Injection Container
     */
    static function init(?Connection $dbal = null): Container
    {
        $dic = new Container();

        $dic['utc_offset'] = 0;

        $dic['dbal'] = $dbal;

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
                $phpmailer->SMTPAuth = SettingsMail::SMTP_AUTH;
                $phpmailer->SMTPAutoTLS = !empty(SettingsMail::SMTP_SECURITY);
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
                $phpmailer->SMTPAuth = SettingsMail::RESERVE_SMTP_AUTH;
                $phpmailer->SMTPAutoTLS = !empty(SettingsMail::RESERVE_SMTP_SECURITY);
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
