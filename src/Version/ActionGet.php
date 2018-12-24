<?php
/**
 * Получим номер последней версии приложения
 *
 * @version 24.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ActionGet
 *
 * @package Lemurro\Api\Core\Version
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

        return Response::data([
            'version' => [
                'android' => intval($version_android, 10),
                'ios'     => intval($version_ios, 10),
            ],
        ]);
    }
}
