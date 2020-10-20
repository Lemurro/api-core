<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileOlderFiles
{
    private array $config_file;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_file)
    {
        $this->config_file = $config_file;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        if (is_dir($this->config_file['path_temp'])) {
            if ($handle = opendir($this->config_file['path_temp'])) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '.' && $file !== '..') {
                        $file_path = $this->config_file['path_temp'] . '/' . $file;
                        $file_time = filemtime($file_path);
                        $file_date = Carbon::createFromTimestamp($file_time, 'UTC');

                        if ($file_date->diffInDays($now) >= $this->config_file['outdated_file_days']) {
                            @unlink($file_path);
                        }
                    }
                }

                closedir($handle);
            }
        }
    }
}
