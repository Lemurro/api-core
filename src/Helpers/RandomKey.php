<?php

/**
 * Генератор случайной строки определенной длины
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 15.09.2020
 */

namespace Lemurro\Api\Core\Helpers;

/**
 * @package Lemurro\Api\Core\Helpers
 */
class RandomKey
{
    /**
     * Сгенерируем строку
     *
     * @param integer $length Длина строки
     *
     * @return string
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 15.09.2020
     */
    static function generate($length = 10)
    {
        $chars = 'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6';
        $end = strlen($chars) - 1;
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[mt_rand(0, $end)];
        }

        return $key;
    }
}
