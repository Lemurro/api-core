<?php
/**
 * Проверка доступа пользователя к файлу
 *
 * @version 31.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Exception;
use Lemurro\Api\Core\Abstracts\Action;
use Monolog\Logger;

/**
 * Class FileRights
 *
 * @package Lemurro\Api\Core\File
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
     * @version 31.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function check($container_type, $container_id)
    {
        if (strtolower($container_type) === 'default') {
            return true;
        }

        $classname = 'Lemurro\\Api\\App\\Checker\\File' . ucfirst($container_type);

        try {
            $class = new $classname($this->dic);

            return call_user_func([$class, 'check'], $container_id);
        } catch (Exception $e) {
            /** @var Logger $log */
            $log = $this->dic['log'];

            $log->error('/File/FileRights.php: Unknown class "' . $classname . '", ' . $e->getMessage());

            return false;
        }
    }
}
