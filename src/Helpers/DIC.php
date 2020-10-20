<?php

/**
 * Инициализация Dependency Injection Container
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\Core\Configuration\ConfigFactory;
use Lemurro\Api\Core\Helpers\SMS\SMS;
use Lemurro\Api\Core\Session;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
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
     * @version 14.10.2020
     */
    public static function init(string $path_root, string $session_id = '', int $utc_offset = 0): Container
    {
        $dic = new Container();

        $dic['path_root'] = preg_replace('/\/$/', '', $path_root);
        $dic['session_id'] = $session_id;
        $dic['utc_offset'] = $utc_offset;

        $dic['config'] = function ($c) {
            return (new ConfigFactory())->create($c['path_root']);
        };

        $dic['logfactory'] = function ($c) {
            return new LoggerFactory($c['config']['file']['path_logs']);
        };

        $dic['log'] = function ($c) {
            /** @var LoggerFactory $logfactory */
            $logfactory = $c['logfactory'];

            return $logfactory->create('Main');
        };

        $dic['user'] = function ($c) {
            if (empty($c['session_id'])) {
                return [];
            }

            $result_session_check = (new Session($c['config']['auth']))->check($c['session_id']);
            if (isset($result_session_check['errors'])) {
                return [];
            }

            $user_info = (new GetUser($c))->run($result_session_check['user_id']);
            if (isset($user_info['data'])) {
                $user_info['data']['admin'] = (isset($user_info['data']['roles']['admin']) ? true : false);

                return $user_info['data'];
            }

            return [];
        };

        $dic['datetimenow'] = function () {
            return Carbon::now('UTC')->toDateTimeString();
        };

        $dic['phpmailer'] = function ($c) {
            $phpmailer = new PHPMailer();
            $phpmailer->isHTML(true);
            $phpmailer->CharSet = 'windows-1251';
            $phpmailer->From = $c['config']['mail']['app_email'];
            $phpmailer->FromName = iconv('utf-8', 'windows-1251', $c['config']['general']['app_name']);

            if ($c['config']['mail']['smtp']) {
                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = $c['config']['mail']['smtp_security'];
                $phpmailer->Host = $c['config']['mail']['smtp_host'];
                $phpmailer->Port = $c['config']['mail']['smtp_port'];
                $phpmailer->Username = $c['config']['mail']['smtp_username'];
                $phpmailer->Password = $c['config']['mail']['smtp_password'];
            }

            return $phpmailer;
        };

        if ($dic['config']['mail']['reserve']) {
            $dic['phpmailer_reserve'] = function ($c) {
                $phpmailer = new PHPMailer();
                $phpmailer->isHTML(true);
                $phpmailer->CharSet = 'windows-1251';
                $phpmailer->From = $c['config']['mail']['reserve_app_email'];
                $phpmailer->FromName = iconv('utf-8', 'windows-1251', $c['config']['general']['app_name']);

                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = $c['config']['mail']['reserve_smtp_security'];
                $phpmailer->Host = $c['config']['mail']['reserve_smtp_host'];
                $phpmailer->Port = $c['config']['mail']['reserve_smtp_port'];
                $phpmailer->Username = $c['config']['mail']['reserve_smtp_username'];
                $phpmailer->Password = $c['config']['mail']['reserve_smtp_password'];

                return $phpmailer;
            };
        }

        $dic['mailer'] = function ($c) {
            return new Mailer($c);
        };

        $dic['sms'] = function ($c) {
            return new SMS($c['config']['sms'], $c['logfactory']);
        };

        $dic['datachangelog'] = function ($c) {
            return new DataChangeLog($c);
        };

        return $dic;
    }
}
