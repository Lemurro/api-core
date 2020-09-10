<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\Guide
 */
trait CheckType
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    protected function checkType(string $type): array
    {
        if (isset(SettingsGuides::CLASSES[$type])) {
            return Response::data([
                'class' => SettingsGuides::CLASSES[$type],
            ]);
        }

        return Response::error404('Неизвестный справочник');
    }
}
