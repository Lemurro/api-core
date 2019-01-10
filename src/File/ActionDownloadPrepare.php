<?php
/**
 * Подготовка файла к скачиванию
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class ActionDownloadPrepare
 *
 * @package Lemurro\Api\Core\File
 */
class ActionDownloadPrepare extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $fileid   ИД файла
     * @param string  $filename Имя файла без расширения (для браузера)
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($fileid, $filename = '')
    {
        $info = (new FileInfo())->getOneORM($fileid);
        if (is_array($info)) {
            return $info;
        }

        if ((new FileRights($this->dic))->check($info->container_type, $info->container_id) === false) {
            return Response::error403('Доступ ограничен', false);
        }

        $file_path = SettingsFile::FILE_FOLDER . $info->path;

        if (is_readable($file_path) && is_file($file_path)) {
            if (empty($filename)) {
                $name = $info->name . '.' . $info->ext;
            } else {
                $pathinfo = pathinfo($filename);

                if ($pathinfo['extension'] === $info->ext) {
                    $name = $filename;
                } else {
                    $name = $filename . '.' . $info->ext;
                }
            }

            $token = (new FileToken($this->dic))->generate($info->path, $name);
            if (empty($token)) {
                return Response::error500('Ключ для скачивания файла не был создан, попробуйте ещё раз');
            }

            return Response::data([
                'token' => $token,
            ]);
        } else {
            return Response::error404('Файл не найден');
        }
    }
}
