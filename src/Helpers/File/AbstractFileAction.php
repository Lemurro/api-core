<?php

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;
use Pimple\Container;

abstract class AbstractFileAction
{
    protected Container $dic;
    protected string $datetimenow;
    protected Logger $log;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->datetimenow = $dic['datetimenow'];
        $this->log = LoggerFactory::create('File');
    }
}
