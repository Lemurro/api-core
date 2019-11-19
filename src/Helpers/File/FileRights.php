<?php
/**
 * Проверка доступа пользователя к файлу
 *
 * @version 08.04.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class FileRights
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRights extends Action
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * FileRights constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->log = LoggerFactory::create('File');
    }

    /**
     * Выполним действие
     *
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function check($container_type, $container_id)
    {
        if (strtolower($container_type) === 'default') {
            return true;
        }

        $classname = 'Lemurro\\Api\\App\\Checker\\File' . ucfirst($container_type);

        if (class_exists($classname)) {
            $class = new $classname($this->dic);

            return call_user_func([$class, 'check'], $container_id);
        } else {
            $this->log->error('/File/FileRights.php: Unknown class "' . $classname . '"');

            return false;
        }
    }
}
