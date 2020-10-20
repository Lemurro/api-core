<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\Version
 */
class ActionGet
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run(string $path_root): array
    {
        $version_android = file_get_contents("$path_root/version.android");
        $version_ios = file_get_contents("$path_root/version.ios");

        if ($version_android === false) {
            $version_android = -1;
        }

        if ($version_ios === false) {
            $version_ios = -1;
        }

        return Response::data([
            'version' => [
                'android' => (string) $version_android,
                'ios' => (string) $version_ios,
            ],
        ]);
    }
}
