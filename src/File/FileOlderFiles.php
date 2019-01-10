<?php
/**
 * Очистим устаревшие файлы во временном каталоге
 *
 * @version 10.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsFile;

/**
 * Class FileOlderFiles
 *
 * @package Lemurro\Api\Core\File
 */
class FileOlderFiles
{
    /**
     * Выполним очистку
     *
     * @version 10.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

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
