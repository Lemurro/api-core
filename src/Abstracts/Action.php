<?php
/**
 * Модель действия
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\Core\Helpers\DataChangeLog;
use Pimple\Container;

/**
 * Class Action
 *
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class Action
{
    /**
     * Контейнер
     *
     * @var Container
     */
    protected $dic;

    /**
     * Строка с датой YYYY-MM-DD HH:MM:SS
     *
     * @var string
     */
    protected $date_time_now;

    /**
     * @var DataChangeLog
     */
    protected $data_change_log;

    /**
     * Конструктор
     *
     * @param Container $dic Контейнер
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        $this->dic = $dic;
        $this->date_time_now = $dic['datetimenow'];
        $this->data_change_log = $dic['datachangelog'];
    }
}
