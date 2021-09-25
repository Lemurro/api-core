<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
abstract class AbstractFileAction
{
    protected Container $dic;
    protected string $datetimenow;
    protected Logger $log;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->datetimenow = $dic['datetimenow'];

        /** @var LoggerFactory $logfactory */
        $logfactory = $dic['logfactory'];
        $this->log = $logfactory->create('File');
    }
}
