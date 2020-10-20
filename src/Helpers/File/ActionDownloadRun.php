<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ActionDownloadRun extends Action
{
    /**
     * @param string token Токен для скачивания файла
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run($token)
    {
        $file_info = (new FileToken($this->dic))->getFileInfo($token);

        if (isset($file_info['errors'])) {
            return $file_info;
        }

        switch ($file_info['data']['type']) {
            case 'permanent':
                $folder = $this->dic['config']['file']['path_upload'];
                break;

            case 'temporary':
                $folder = $this->dic['config']['file']['path_temp'];
                break;

            default:
                return Response::error404('Файл не найден');
                break;
        }

        $filepath = $folder . '/' . $file_info['data']['path'];

        if (!is_readable($filepath) || !is_file($filepath)) {
            return Response::error404('Файл не найден');
        }

        return Response::data([
            'filepath' => $filepath,
            'filename' => $file_info['data']['name'],
        ]);
    }
}
