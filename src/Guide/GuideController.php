<?php

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Шаблон контроллера
 */
abstract class GuideController extends Controller
{
    /**
     * @throws \RuntimeException
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 04.11.2020
     */
    protected function checkType(string $type): string
    {
        if (isset(SettingsGuides::CLASSES[$type])) {
            return SettingsGuides::CLASSES[$type];
        }

        throw new \RuntimeException('Неизвестный справочник', 404);
    }
}
