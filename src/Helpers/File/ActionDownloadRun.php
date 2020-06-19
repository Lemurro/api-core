<?php

/**
 * Скачивание файла
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class ActionDownloadRun
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class ActionDownloadRun extends Action
{
    /**
     * Выполним действие
     *
     * @param string token Токен для скачивания файла
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function run($token)
    {
        $file_info = (new FileToken($this->dic))->getFileInfo($token);

        if (!$file_info['success']) {
            return $file_info;
        }

        switch ($file_info['data']['type']) {
            case 'permanent':
                $folder = SettingsFile::FILE_FOLDER;
                break;

            case 'temporary':
                $folder = SettingsFile::TEMP_FOLDER;
                break;

            default:
                return Response::error404('Файл не найден');
                break;
        }

        $filepath = $folder . $file_info['data']['path'];

        if (!is_readable($filepath) || !is_file($filepath)) {
            return Response::error404('Файл не найден');
        }

        return Response::data([
            'filepath' => $filepath,
            'filename' => $file_info['data']['name'],
        ]);
    }
}
