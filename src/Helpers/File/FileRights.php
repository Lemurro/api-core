<?php
/**
 * Проверка доступа пользователя к файлу
 *
 * @version 08.04.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class FileRights
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRights extends Action
{
    /**
     * Выполним действие
     *
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return boolean
     *
     * @version 08.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function check($container_type, $container_id)
    {
        if (strtolower($container_type) === 'default') {
            return true;
        }

        (new ContainerType())->validate($container_type);

        $classname = 'Lemurro\\Api\\App\\Checker\\File' . ucfirst($container_type);
        $class = new $classname($this->dic);

        return call_user_func([$class, 'check'], $container_id);
    }
}
