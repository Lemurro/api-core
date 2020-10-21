<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 21.10.2020
 */

namespace Lemurro\Api\Core\Configuration;

/**
 * @package Lemurro\Api\Core\Configuration
 */
class Preparing
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 21.10.2020
     */
    public static function run(array $config): array
    {
        if (isset($config['file'])) {
            if (isset($config['file']['path_logs'])) {
                $config['file']['path_logs'] = preg_replace('/\/$/', '', $config['file']['path_logs']);
            }

            if (isset($config['file']['path_temp'])) {
                $config['file']['path_temp'] = preg_replace('/\/$/', '', $config['file']['path_temp']);
            }

            if (isset($config['file']['path_upload'])) {
                $config['file']['path_upload'] = preg_replace('/\/$/', '', $config['file']['path_upload']);
            }
        }

        return $config;
    }
}
