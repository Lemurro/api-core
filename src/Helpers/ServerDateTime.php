<?php
/**
 * Получение серверного времени из времени переданного в виде параметра
 *
 * @version 13.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class ServerDateTime
 *
 * @package Lemurro\Api\Core\Helpers
 */
class ServerDateTime extends Action
{
    /**
     * Сгенерируем число
     *
     * @param string $input_value   Входная строка
     * @param string $input_format  Входной формат
     * @param string $output_format Формат вывода
     *
     * @return string
     *
     * @version 13.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function get($input_value, $input_format, $output_format = 'Y-m-d H:i:s')
    {
        $utc_offset = $this->dic['utc_offset'];

        if ($utc_offset == 0) {
            return $this->dic['datetimenow'];
        }

        $dt = Carbon::createFromFormat($input_format, $input_value);

        if ($utc_offset > 0) {
            $dt->subMinutes($utc_offset);
        } else {
            $dt->addMinutes($utc_offset);
        }

        return $dt->format($output_format);
    }
}
