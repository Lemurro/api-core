<?php
/**
 * Создание уникального имени для файла (с проверкой на дубликаты)
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

/**
 * Class FileName
 *
 * @package Lemurro\Api\Core\File
 */
class FileName
{
    /**
     * Выполним действие
     *
     * @param string $dest_folder Каталог для файла
     * @param string $dest_name   Имя файла
     * @param string $orig_ext    Расширение файла
     *
     * @return string
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function generate($dest_folder, $dest_name, $orig_ext)
    {
        // Убираем из имени файла цифры в скобках, если они есть
        $dest_name = preg_replace('/\(.+\)/i', '', $dest_name);

        $file_name = $dest_name . '.' . $orig_ext;

        $check = $dest_folder . $file_name;
        $inc = 1;
        while (is_readable($check) && is_file($check)) {
            $file_name = $dest_name . '(' . $inc . ').' . $orig_ext;
            $check = $dest_folder . $file_name;
            $inc++;
        }

        return $file_name;
    }
}
