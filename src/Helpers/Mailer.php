<?php

/**
 * Отправка электронных писем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\Core\EmailTemplates\EmailTemplates;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;
use RuntimeException;
use Throwable;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class Mailer
{
    private string $path_root;
    private array $config;
    private bool $is_reserve;
    private PHPMailer $phpmailer;
    private PHPMailer $phpmailer_reserve;
    private Logger $log;
    private string $template;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    public function __construct(Container $dic, string $template = '')
    {
        $this->path_root = $dic['path_root'];
        $this->config = $dic['config'];
        $this->is_reserve = $this->config['mail']['reserve'];
        $this->phpmailer = $dic['phpmailer'];
        $this->log = $dic['logfactory']->create('Mailer');

        $this->email_template = new EmailTemplates($this->path_root);

        $this->template = !empty($template) ? $template : $this->email_template->getTpl('_template');

        $logo = $this->email_template->getTpl('logo.base64');
        $this->template = strtr($this->template, ['[__LOGO_BASE64__]' => $logo]);

        if ($this->is_reserve) {
            $this->phpmailer_reserve = $dic['phpmailer_reserve'];
        }
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    public function send(string $tpl_file_name, string $subject, array $email_tos, array $template_data, array $images = [], array $files = []): bool
    {
        if ($this->config['general']['server_type'] === $this->config['general']['const_server_type_dev'] && $this->config['mail']['smtp'] === false) {
            return true;
        }

        try {
            $this->clear();

            $this->attachRecepients($email_tos);
            $this->attachImages($images);
            $this->attachFiles($files);

            $tpl = $this->email_template->getTpl($tpl_file_name);
            $body = strtr($tpl, $template_data);
            $message = strtr($this->template, ['[__BODY__]' => $body]);

            $this->phpmailer->Subject = iconv('utf-8', 'windows-1251', $subject);
            $this->phpmailer->MsgHTML(iconv('utf-8', 'windows-1251', $message));

            if ($this->is_reserve) {
                $this->phpmailer_reserve->Subject = iconv('utf-8', 'windows-1251', $subject);
                $this->phpmailer_reserve->MsgHTML(iconv('utf-8', 'windows-1251', $message));
            }

            if ($this->phpmailer->Send()) {
                return true;
            } else {
                $this->log->warning('При отправке письма через основной канал произошла ошибка: "' . $this->phpmailer->ErrorInfo . '".');
            }

            if ($this->is_reserve) {
                if ($this->phpmailer_reserve->Send()) {
                    return true;
                } else {
                    $this->log->warning('При отправке письма через резервный канал произошла ошибка: "' . $this->phpmailer_reserve->ErrorInfo . '".');
                }
            }
        } catch (Throwable $t) {
            LogException::write($this->log, $t);
        }

        return false;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    private function clear(): void
    {
        $this->phpmailer->ClearAllRecipients();
        $this->phpmailer->ClearAttachments();
        $this->phpmailer->ClearCustomHeaders();
        $this->phpmailer->ClearReplyTos();

        if ($this->is_reserve) {
            $this->phpmailer_reserve->ClearAllRecipients();
            $this->phpmailer_reserve->ClearAttachments();
            $this->phpmailer_reserve->ClearCustomHeaders();
            $this->phpmailer_reserve->ClearReplyTos();
        }
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    private function attachRecepients(array $email_tos): void
    {
        if (empty($email_tos)) {
            throw new RuntimeException('Массив адресов отправки пуст', 400);
        }

        if (is_countable($email_tos)) {
            foreach ($email_tos as $one_email) {
                $this->phpmailer->addAddress($one_email);

                if ($this->is_reserve) {
                    $this->phpmailer_reserve->addAddress($one_email);
                }
            }
        }
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    private function attachImages(array $images): void
    {
        if (is_countable($images)) {
            foreach ($images as $image_code => $image_filename) {
                $filename = "$this->path_root/$image_filename";
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

                    if ($this->is_reserve) {
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
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    private function attachFiles(array $files): void
    {
        if (is_countable($files)) {
            foreach ($files as $filename) {
                if (is_readable($filename)) {
                    try {
                        $this->phpmailer->addAttachment($filename);

                        if ($this->is_reserve) {
                            $this->phpmailer_reserve->addAttachment($filename);
                        }
                    } catch (Throwable $t) {
                    }
                }
            }
        }
    }
}
