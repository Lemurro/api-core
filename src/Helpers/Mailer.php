<?php

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\App\Configs\EmailTemplates;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\App\Configs\SettingsPath;
use Monolog\Logger;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;
use Throwable;

/**
 * Отправка электронных писем
 */
class Mailer
{
    /**
     * @var PHPMailer
     */
    protected $phpmailer;

    /**
     * @var PHPMailer
     */
    protected $phpmailer_reserve;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var string HTML-код шапки письма
     */
    protected $header;

    /**
     * @var string HTML-код подвала письма
     */
    protected $footer;

    /**
     * @param Container $dic Контейнер
     * @param string $header HTML-код шапки письма
     * @param string $footer HTML-код подвала письма
     */
    public function __construct($dic, $header = EmailTemplates::HEADER, $footer = EmailTemplates::FOOTER)
    {
        $this->phpmailer = $dic['phpmailer'];
        $this->log = LoggerFactory::create('Mailer');
        $this->header = $header;
        $this->footer = $footer;

        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsMail::RESERVE) {
            $this->phpmailer_reserve = $dic['phpmailer_reserve'];
        }
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
     */
    public function send($template_name, $subject, $email_tos, $template_data, $images = [], $files = []): bool
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsMail::SMTP === false) {
            return true;
        }

        // Проверяем наличие шаблона
        try {
            $template = constant('\Lemurro\Api\App\Configs\EmailTemplates::' . $template_name);
        } catch (Throwable $th) {
            LogException::write($this->log, $th);

            return false;
        }

        // Очищаемся от старых данных
        $this->phpmailer->ClearAllRecipients();
        $this->phpmailer->ClearAttachments();
        $this->phpmailer->ClearCustomHeaders();
        $this->phpmailer->ClearReplyTos();

        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsMail::RESERVE) {
            $this->phpmailer_reserve->ClearAllRecipients();
            $this->phpmailer_reserve->ClearAttachments();
            $this->phpmailer_reserve->ClearCustomHeaders();
            $this->phpmailer_reserve->ClearReplyTos();
        }

        // Прикрепляем другие изображения при необходимости
        if (is_array($images) && count($images) > 0) {
            foreach ($images as $image_code => $image_filename) {
                $filename = SettingsPath::FULL_ROOT . $image_filename;
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

                    /** @psalm-suppress TypeDoesNotContainType */
                    if (SettingsMail::RESERVE) {
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

                        /** @psalm-suppress TypeDoesNotContainType */
                        if (SettingsMail::RESERVE) {
                            $this->phpmailer_reserve->addAttachment($filename);
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        // Связываем данные с шаблоном
        $header_content = strtr($this->header, ['[LOGO_BASE64]' => $this->getBase64Logo()]);
        $body_content = strtr($template, $template_data);
        $footer_content = $this->footer;
        $message = $header_content . $body_content . $footer_content;

        $this->phpmailer->Subject = iconv('utf-8', 'windows-1251', $subject);
        $this->phpmailer->MsgHTML(iconv('utf-8', 'windows-1251', $message));

        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsMail::RESERVE) {
            $this->phpmailer_reserve->Subject = iconv('utf-8', 'windows-1251', $subject);
            $this->phpmailer_reserve->MsgHTML(iconv('utf-8', 'windows-1251', $message));
        }

        if (empty($email_tos)) {
            $this->log->warning('Массив адресов отправки пуст.');

            return false;
        }

        foreach ($email_tos as $one_email) {
            $this->phpmailer->addAddress($one_email);

            /** @psalm-suppress TypeDoesNotContainType */
            if (SettingsMail::RESERVE) {
                $this->phpmailer_reserve->addAddress($one_email);
            }
        }

        try {
            if ($this->phpmailer->Send()) {
                return true;
            }

            $this->log->warning('При отправке письма через основной канал произошла ошибка: "' . $this->phpmailer->ErrorInfo . '".');
        } catch (Exception $e) {
            LogException::write($this->log, $e);
        }

        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsMail::RESERVE) {
            try {
                if ($this->phpmailer_reserve->Send()) {
                    return true;
                }

                $this->log->warning('При отправке письма через резервный канал произошла ошибка: "' . $this->phpmailer_reserve->ErrorInfo . '".');
            } catch (Exception $e) {
                LogException::write($this->log, $e);
            }
        }

        return false;
    }

    protected function getBase64Logo(): string
    {
        $path = SettingsPath::FULL_ROOT . 'public/logo.png';
        if (is_file($path) and is_readable($path)) {
            return sprintf(
                "data:image/%s;base64,%s",
                pathinfo($path, PATHINFO_EXTENSION),
                base64_encode(
                    file_get_contents($path)
                ),
            );
        }

        // 1pixel.png
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAC0lEQVQI12NgAAIAAAUAAeImBZsAAAAASUVORK5CYII=';
    }
}
