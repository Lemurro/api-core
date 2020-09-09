<?php

/**
 * Интерфейс проверки доступа к файлу
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Checker\Checker;
use Pimple\Container;

/**
 * Class FileChecker
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
abstract class FileChecker
{
    protected Container $dic;
    protected Checker $checker;

    /**
     * @param Container $dic Контейнер
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
     */
    public function __construct($dic)
    {
        $this->dic = $dic;

        $this->checker = new Checker($dic);
    }

    /**
     * Проверка прав доступа
     *
     * @param string $container_id ИД контейнера
     *
     * @return boolean
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    abstract public function check($container_id);
}
