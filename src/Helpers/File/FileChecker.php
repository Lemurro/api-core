<?php
/**
 * Интерфейс проверки доступа к файлу
 *
 * @version 28.03.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Checker\Checker;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class FileChecker
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
abstract class FileChecker
{
    /**
     * Контейнер
     *
     * @var Container
     */
    protected $dic;

    protected Checker $checker;
    protected Logger $log;

    /**
     * Конструктор
     *
     * @param Container $dic Контейнер
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct($dic)
    {
        $this->dic = $dic;
        $this->checker = new Checker($dic);
        $this->log = $dic['logfactory']->create('FileChecker');
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
