<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRights extends Action
{
    /**
     * Проверка доступа пользователя к файлу
     *
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return boolean
     *
     * @throws RuntimeException
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
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
