<?php

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Получим номер последней версии приложения
 */
class ActionGet
{
    /**
     * Выполним действие
     *
     * @return array
     *
     * @version 24.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run()
    {
        $version_android = file_get_contents(SettingsPath::FULL_ROOT . 'version.android');
        $version_ios = file_get_contents(SettingsPath::FULL_ROOT . 'version.ios');

        if ($version_android === false) {
            $version_android = -1;
        }

        if ($version_ios === false) {
            $version_ios = -1;
        }

        return Response::data([
            'version' => [
                'android' => (string) $version_android,
                'ios'     => (string) $version_ios,
            ],
        ]);
    }
}
