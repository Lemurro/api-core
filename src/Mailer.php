<?php
/**
 * Отправка электронных писем
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Lemurro\Api\App\Configs\EmailTemplates;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Mailer
 *
 * @package Lemurro\Api\Core
 */
class Mailer
{
    /**
     * Контейнер
     *
     * @var object
     */
    protected $dic;

    /**
     * Логгер
     *
     * @var object
     */
    protected $log;

    /**
     * Конструктор
     *
     * @param object $dic Контейнер
     *
     * @throws \Exception
     *
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct($dic)
    {
        $this->dic = $dic;

        $this->log = new Logger('Mailer');
        $this->log->pushHandler(new StreamHandler(SettingsGeneral::FULL_ROOT_PATH . 'logs/mailer.log'));
    }

    /**
     * Отправка письма
     *
     * @param string $template_name Имя шаблона
     * @param string $subject       Тема письма
     * @param array  $email_tos     Массив адресов кому отправить письмо
     * @param array  $template_data Массив данных для подстановки в шаблон
     * @param array  $images        Массив изображений для вставки в письмо
     * @param array  $files         Массив файлов для прикрепления к письму
     *
     * @return boolean
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($template_name, $subject, $email_tos, $template_data, $images = [], $files = [])
    {
        // Проверяем наличие шаблона
        if (constant('\Lemurro\Api\App\Configs\EmailTemplates::' . $template_name) !== null) {
            if (SettingsGeneral::PRODUCTION === false AND SettingsMail::SMTP === false) {
                return true;
            } else {
                // Очищаемся от старых данных
                $this->dic['phpmailer']->ClearAllRecipients();
                $this->dic['phpmailer']->ClearAttachments();
                $this->dic['phpmailer']->ClearCustomHeaders();
                $this->dic['phpmailer']->ClearReplyTos();

                // Прикрепляем логотип для письма
                $logo_mail = SettingsGeneral::FULL_ROOT_PATH . 'assets/img/logo.png';
                if (is_readable($logo_mail)) {
                    $this->dic['phpmailer']->AddEmbeddedImage($logo_mail, 'logotype', 'logo.png', 'base64', 'image/png');
                }

                // Прикрепляем другие изображения при необходимости
                if (count($images) > 0) {
                    foreach ($images as $image_code => $image_filename) {
                        $filename = SettingsGeneral::FULL_ROOT_PATH . $image_filename;
                        if (is_readable($filename)) {
                            $basename = pathinfo($filename, PATHINFO_BASENAME);
                            $imagetype = getimagesize($filename)['mime'];
                            $this->dic['phpmailer']->AddEmbeddedImage($filename, $image_code, $basename, 'base64', $imagetype);
                        }
                    }
                }

                // Прикрепляем файлы при необходимости
                if (count($files) > 0) {
                    foreach ($files as $filename) {
                        if (is_readable($filename)) {
                            $this->dic['phpmailer']->addAttachment($filename);
                        }
                    }
                }

                // Связываем данные с шаблоном
                $message = EmailTemplates::HEADER;
                $message .= strtr(constant('\Lemurro\Api\App\Configs\EmailTemplates::' . $template_name), $template_data);
                $message .= EmailTemplates::FOOTER;

                $this->dic['phpmailer']->Subject = iconv('utf-8', 'windows-1251', $subject);
                $this->dic['phpmailer']->MsgHTML(iconv('utf-8', 'windows-1251', $message));

                if (count($email_tos) > 0) {
                    foreach ($email_tos as $one_email) {
                        $this->dic['phpmailer']->addAddress($one_email);
                    }
                } else {
                    $this->log->warning('Массив адресов отправки пуст.');

                    return false;
                }

                if (!$this->dic['phpmailer']->Send()) {
                    $this->log->warning('При отправке письма произошла ошибка: "' . $this->dic['phpmailer']->ErrorInfo . '".');

                    return false;
                } else {
                    return true;
                }
            }
        } else {
            $this->log->warning('При отправке письма не найден шаблон: "' . $template_name . '".');

            return false;
        }
    }
}
