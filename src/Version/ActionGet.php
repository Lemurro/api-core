<?php
/**
 * Получим номер последней версии приложения
 *
 * @version 01.01.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\App\Configs\SettingsGeneral;

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
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run()
    {
        $version_android = file_get_contents(SettingsGeneral::FULL_ROOT_PATH . 'version.android');
        $version_ios = file_get_contents(SettingsGeneral::FULL_ROOT_PATH . 'version.ios');

        return [
            'data' => [
                'version' => [
                    'android' => intval($version_android, 10),
                    'ios'     => intval($version_ios, 10),
                ],
            ],
        ];
    }
}
