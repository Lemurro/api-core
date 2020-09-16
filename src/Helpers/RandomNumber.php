<?php

/**
 * Генератор случайного числа определенной длины
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 15.09.2020
 */

namespace Lemurro\Api\Core\Helpers;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class RandomNumber
{
    /**
     * Сгенерируем число
     *
     * @param integer $length Длина строки
     *
     * @return integer
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 15.09.2020
     */
    public static function generate($length = 10)
    {
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= mt_rand(1, 9);
        }

        return intval($key, 10);
    }
}
