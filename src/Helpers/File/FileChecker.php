<?php

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Checker\Checker;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;
use Pimple\Container;

/**
 * Интерфейс проверки доступа к файлу
 */
abstract class FileChecker
{
    protected Container $dic;
    protected Checker $checker;
    protected Logger $log;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->checker = new Checker($dic);
        $this->log = LoggerFactory::create('FileChecker');
    }

    /**
     * Проверка прав доступа
     *
     * @param string $container_id ИД контейнера
     *
     * @return boolean
     */
    abstract public function check($container_id);
}
