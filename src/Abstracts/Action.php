<?php

/**
 * Модель действия
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class Action
{
    protected Container $dic;
    protected string $datetimenow;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;

        $this->datetimenow = $dic['datetimenow'];
    }
}
