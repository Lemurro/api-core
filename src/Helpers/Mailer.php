<?php

/**
 * Отправка электронных писем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\EmailTemplates\Footer;
use Lemurro\Api\App\Configs\EmailTemplates\Header;
use Lemurro\Api\App\Configs\EmailTemplates\LogoBase64;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\App\Configs\SettingsPath;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class Mailer
{
    protected PHPMailer $phpmailer;
    protected PHPMailer $phpmailer_reserve;
    protected Logger $log;
    protected string $header;
    protected string $footer;

    /**
     * @param object $dic    Контейнер
     * @param string $header HTML-код шапки письма
     * @param string $footer HTML-код подвала письма
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function __construct($dic, $header = Header::$tpl, $footer = Footer::$tpl)
    {
        $this->phpmailer = $dic['phpmailer'];
        $this->log = LoggerFactory::create('Mailer');
        $this->header = $header;
        $this->footer = $footer;

        if (SettingsMail::$reserve) {
            $this->phpmailer_reserve = $dic['phpmailer_reserve'];
        }
    }

    /**
     * @param string $tpl           Шаблон
     * @param string $subject       Тема письма
     * @param array  $email_tos     Массив адресов кому отправить письмо
     * @param array  $template_data Массив данных для подстановки в шаблон
     * @param array  $images        Массив изображений для вставки в письмо
     * @param array  $files         Массив файлов для прикрепления к письму
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function send($tpl, $subject, $email_tos, $template_data, $images = [], $files = [])
    {
        if (SettingsGeneral::$server_type === SettingsGeneral::$server_type_dev && SettingsMail::$smtp === false) {
            return true;
        } else {
            // Очищаемся от старых данных
            $this->phpmailer->ClearAllRecipients();
            $this->phpmailer->ClearAttachments();
            $this->phpmailer->ClearCustomHeaders();
            $this->phpmailer->ClearReplyTos();

            if (SettingsMail::$reserve) {
                $this->phpmailer_reserve->ClearAllRecipients();
                $this->phpmailer_reserve->ClearAttachments();
                $this->phpmailer_reserve->ClearCustomHeaders();
                $this->phpmailer_reserve->ClearReplyTos();
            }

            // Прикрепляем другие изображения при необходимости
            if (is_array($images) && count($images) > 0) {
                foreach ($images as $image_code => $image_filename) {
                    $filename = SettingsPath::$root . $image_filename;
                    if (is_readable($filename)) {
                        $basename = pathinfo($filename, PATHINFO_BASENAME);
                        $imagetype = getimagesize($filename)['mime'];

                        $this->phpmailer->AddEmbeddedImage(
                            $filename,
                            $image_code,
                            $basename,
                            'base64',
                            $imagetype
                        );

                        if (SettingsMail::$reserve) {
                            $this->phpmailer_reserve->AddEmbeddedImage(
                                $filename,
                                $image_code,
                                $basename,
                                'base64',
                                $imagetype
                            );
                        }
                    }
                }
            }

            // Прикрепляем файлы при необходимости
            if (is_array($files) && count($files) > 0) {
                foreach ($files as $filename) {
                    if (is_readable($filename)) {
                        try {
                            $this->phpmailer->addAttachment($filename);

                            if (SettingsMail::$reserve) {
                                $this->phpmailer_reserve->addAttachment($filename);
                            }
                        } catch (Throwable $t) {
                        }
                    }
                }
            }

            // Связываем данные с шаблоном
            $header_content = strtr($this->header, ['[LOGO_BASE64]' => LogoBase64::$tpl]);
            $body_content = strtr($tpl, $template_data);
            $footer_content = $this->footer;
            $message = $header_content . $body_content . $footer_content;

            $this->phpmailer->Subject = iconv('utf-8', 'windows-1251', $subject);
            $this->phpmailer->MsgHTML(iconv('utf-8', 'windows-1251', $message));

            if (SettingsMail::$reserve) {
                $this->phpmailer_reserve->Subject = iconv('utf-8', 'windows-1251', $subject);
                $this->phpmailer_reserve->MsgHTML(iconv('utf-8', 'windows-1251', $message));
            }

            if (is_array($email_tos) && count($email_tos) > 0) {
                foreach ($email_tos as $one_email) {
                    $this->phpmailer->addAddress($one_email);

                    if (SettingsMail::$reserve) {
                        $this->phpmailer_reserve->addAddress($one_email);
                    }
                }
            } else {
                $this->log->warning('Массив адресов отправки пуст.');

                return false;
            }

            try {
                if (!$this->phpmailer->Send()) {
                    $this->log->warning('При отправке письма через основной канал произошла ошибка: "' . $this->phpmailer->ErrorInfo . '".');
                } else {
                    return true;
                }
            } catch (Throwable $t) {
                LogException::write($this->log, $t);
            }

            if (SettingsMail::$reserve) {
                try {
                    if (!$this->phpmailer_reserve->Send()) {
                        $this->log->warning('При отправке письма через резервный канал произошла ошибка: "' . $this->phpmailer_reserve->ErrorInfo . '".');
                    } else {
                        return true;
                    }
                } catch (Throwable $t) {
                    LogException::write($this->log, $t);
                }
            }

            return false;
        }
    }
}
