<?php
/**
 * Получение локального времени из серверного времени переданного в виде параметра
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Helpers;

use Carbon\Carbon;
use Lemurro\Api\Core\Abstracts\Action;
use Pimple\Container;

/**
 * Class LocalDateTime
 *
 * @package Lemurro\Api\Core\Helpers
 */
class LocalDateTime extends Action
{
    /**
     * @var array
     */
    protected $utc_offset;

    /**
     * LocalDateTime constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->utc_offset = $this->dic['utc_offset'];
    }

    /**
     * Получим серверное время
     *
     * @param string $input_value   Входная строка
     * @param string $input_format  Входной формат
     * @param string $output_format Формат вывода
     *
     * @return string
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function get($input_value, $input_format, $output_format = 'Y-m-d H:i:s')
    {
        $dt = Carbon::createFromFormat($input_format, $input_value);

        if ($this->utc_offset == 0) {
            return $dt->format($output_format);
        }

        if ($this->utc_offset > 0) {
            $dt->addMinutes($this->utc_offset);
        } else {
            $dt->subMinutes($this->utc_offset);
        }

        return $dt->format($output_format);
    }
}
