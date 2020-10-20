<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 13.10.2020
 */

namespace Lemurro\Api\Core\EmailTemplates;

use RuntimeException;

/**
 * @package Lemurro\Api\Core\EmailTemplates
 */
class EmailTemplates
{
    private string $path_root;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    public function __construct(string $path_root)
    {
        $this->path_root = $path_root;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    public function getTpl(string $file_name): string
    {
        $file_name = strtolower($file_name);

        if ($file_name !== 'logo.base64') {
            if (!$this->validateFilename($file_name)) {
                throw new RuntimeException('Filename incorrect', 400);
            }

            $file_name .= '.html';
        }

        $tpl_file = $this->getFile("$this->path_root/app/Overrides/Configs/templates/$file_name");

        if (empty($tpl_file)) {
            $tpl_file = $this->getFile(__DIR__ . "/../Configs/templates/$file_name");
        }

        if (empty($tpl_file)) {
            throw new RuntimeException('Template file no found', 404);
        }

        return file_get_contents($tpl_file);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    private function validateFilename(string $file_name): bool
    {
        if (!preg_match('/[^\w]/', $file_name)) {
            return true;
        }

        return false;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 13.10.2020
     */
    private function getFile(string $filepath): string
    {
        if (is_file($filepath) && is_readable($filepath)) {
            return $filepath;
        }

        return '';
    }
}
