<?php

namespace Lemurro\Api\Core\Abstracts;

use Doctrine\DBAL\Connection;
use Pimple\Container;

/**
 * Модель действия
 */
abstract class Action
{
    protected Container $dic;
    protected Connection $dbal;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->dbal = $dic['dbal'];
    }
}
