<?php

/**
 * Интерфейс проверки доступа к файлу
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Checker\Checker;
use Monolog\Logger;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
abstract class FileChecker
{
    protected Container $dic;
    protected Checker $checker;
    protected Logger $log;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;

        $this->checker = new Checker($dic);
        $this->log = $dic['logfactory']->create('FileChecker');
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    abstract public function check(string $container_id): bool;
}
