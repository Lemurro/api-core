<?php

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * Получение серверного времени из локального времени переданного в виде параметра
 */
class ServerDateTime extends Action
{
    /**
     * Получим серверное время
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
        $dt = Carbon::createFromFormat($input_format, $input_value);

        if ($utc_offset == 0) {
            return $dt->format($output_format);
        }

        if ($utc_offset > 0) {
            $dt->subMinutes($utc_offset);
        } else {
            $dt->addMinutes($utc_offset);
        }

        return $dt->format($output_format);
    }
}
