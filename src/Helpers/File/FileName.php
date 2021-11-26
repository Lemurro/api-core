<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileName
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function generate(string $dest_folder, string $dest_name, string $orig_ext): string
    {
        // Убираем из имени файла цифры в скобках, если они есть
        $dest_name = preg_replace('/\(.+\)/i', '', $dest_name);

        $file_name = $dest_name . '.' . $orig_ext;

        $check = $dest_folder . '/' . $file_name;
        $inc = 1;
        while (is_readable($check) && is_file($check)) {
            $file_name = $dest_name . '(' . $inc . ').' . $orig_ext;
            $check = $dest_folder . '/' . $file_name;
            $inc++;
        }

        return $file_name;
    }
}
