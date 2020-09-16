<?php

/**
 * Очистим устаревшие файлы во временном каталоге
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 20.01.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsFile;

/**
 * Class FileOlderFiles
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileOlderFiles
{
    /**
     * Выполним очистку
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 20.01.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        if (is_dir(SettingsFile::TEMP_FOLDER)) {
            if ($handle = opendir(SettingsFile::TEMP_FOLDER)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '.' && $file !== '..') {
                        $file_path = SettingsFile::TEMP_FOLDER . $file;
                        $file_time = filemtime($file_path);
                        $file_date = Carbon::createFromTimestamp($file_time, 'UTC');

                        if ($file_date->diffInDays($now) >= SettingsFile::OUTDATED_FILE_DAYS) {
                            @unlink($file_path);
                        }
                    }
                }

                closedir($handle);
            }
        }
    }
}
