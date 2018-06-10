<?php
/**
 * Модель действия
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Abstracts;

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
     * Конструктор
     *
     * @param Container $dic Контейнер
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct($dic)
    {
        $this->dic = $dic;
    }
}
