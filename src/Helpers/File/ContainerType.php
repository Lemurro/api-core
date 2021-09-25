<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use RuntimeException;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ContainerType
{
    /**
     * @throws RuntimeException
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
     */
    public function validate(string $container_type): void
    {
        if (mb_strlen($container_type, 'UTF-8') > 255) {
            throw new RuntimeException('The maximum length of the container_type option should be less than 255 characters', 400);
        }

        $class_name = 'Lemurro\\Api\\App\\Checker\\File' . ucfirst($container_type);
        if (!class_exists($class_name)) {
            throw new RuntimeException('Unknown class "' . $class_name . '"', 404);
        }
    }
}
