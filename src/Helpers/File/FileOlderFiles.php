<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsFile;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileOlderFiles
{
    /**
     * Выполним очистку
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        if (is_dir(SettingsFile::$temp_folder)) {
            if ($handle = opendir(SettingsFile::$temp_folder)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '.' && $file !== '..') {
                        $file_path = SettingsFile::$temp_folder . $file;
                        $file_time = filemtime($file_path);
                        $file_date = Carbon::createFromTimestamp($file_time, 'UTC');

                        if ($file_date->diffInDays($now) >= SettingsFile::$outdated_file_days) {
                            @unlink($file_path);
                        }
                    }
                }

                closedir($handle);
            }
        }
    }
}
