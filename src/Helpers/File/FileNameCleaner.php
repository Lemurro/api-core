<?php

/**
 * Очистка имени файла
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 27.05.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

/**
 * Class FileNameCleaner
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileNameCleaner
{
    /**
     * Очистка имени файла
     *
     * @param string $name Имя файла (без расширения)
     *
     * @return string
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 27.05.2020
     */
    public static function clean($name)
    {
        // Обрезаем специальные символы :"<>*?|\/
        $name = preg_replace('/[:"<>*?|\\\\\/]/', '', $name);

        // Обрезаем длину до 100 символов
        $name = mb_substr($name, 0, 100, 'UTF-8');

        return $name;
    }
}
