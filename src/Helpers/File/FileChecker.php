<?php
/**
 * Интерфейс проверки доступа к файлу
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
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
    /**
     * Контейнер
     *
     * @var Container
     */
    protected $dic;

    /**
     * @var Checker
     */
    protected $checker;

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
        $this->checker = $dic['checker'];
    }

    /**
     * Проверка прав доступа
     *
     * @param string $container_id ИД контейнера
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 08.01.2019
     */
    abstract public function check($container_id);
}
