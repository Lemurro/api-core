<?php

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\Core\Abstracts\Action;

class LocalDateTime extends Action
{
    /**
     * Получение локального времени из серверного времени переданного в виде параметра
     *
     * @param string $input_value   Входная строка
     * @param string $input_format  Входной формат
     * @param string $output_format Формат вывода
     *
     * @return string
     */
    public function get($input_value, $input_format, $output_format = 'Y-m-d H:i:s')
    {
        $utc_offset = (int) $this->dic['utc_offset'];
        $dt = Carbon::createFromFormat($input_format, $input_value);

        if ($utc_offset === 0) {
            return $dt->format($output_format);
        }

        if ($utc_offset > 0) {
            $dt->addMinutes($utc_offset);
        } else {
            $dt->subMinutes($utc_offset);
        }

        return $dt->format($output_format);
    }
}
