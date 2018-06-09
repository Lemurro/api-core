<?php
/**
 * Генератор случайного числа определенной длины
 *
 * @version 01.01.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

/**
 * Class RandomNumber
 *
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
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function generate($length = 10)
    {
        $chars = "123456789";
        $key = "";

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars{mt_rand(0, strlen($chars) - 1)};
        }

        return intval($key, 10);
    }
}
