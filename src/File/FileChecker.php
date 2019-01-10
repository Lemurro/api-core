<?php
/**
 * Интерфейс проверки доступа к файлу
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Pimple\Container;

/**
 * Class FileChecker
 *
 * @package Lemurro\Api\Core\File
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
