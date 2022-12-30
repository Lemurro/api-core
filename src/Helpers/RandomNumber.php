<?php

namespace Lemurro\Api\Core\Helpers;

/**
 * Генератор случайного числа определенной длины
 */
class RandomNumber
{
    /**
     * Сгенерируем число
     *
     * @param integer $length Длина строки
     *
     * @return integer
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
