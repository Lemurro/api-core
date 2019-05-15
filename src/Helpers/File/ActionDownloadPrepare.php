<?php
/**
 * Подготовка файла к скачиванию
 *
 * @version 15.05.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class ActionDownloadPrepare
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class ActionDownloadPrepare extends Action
{
    /**
     * Выполним действие
     *
     * @param integer|string $fileid   ИД постоянного файла или имя временого файла
     * @param string         $filename Имя файла без расширения (для браузера)
     *
     * @return array
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($fileid, $filename = '')
    {
        if (is_int($fileid)) {
            return $this->permanentFile($fileid, $filename);
        } else {
            return $this->temporaryFile($fileid, $filename);
        }
    }

    /**
     * Постоянный файл
     *
     * @param integer $fileid   ИД постоянного файла
     * @param string  $filename Имя файла без расширения (для браузера)
     *
     * @return array
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function permanentFile($fileid, $filename)
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

            $token = (new FileToken($this->dic))->generate('permanent', $info->path, $name);
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

    /**
     * Временный файл
     *
     * @param string $fileid   Имя временого файла
     * @param string $filename Имя файла без расширения (для браузера)
     *
     * @return array
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function temporaryFile($fileid, $filename)
    {
        // f1f2ac7361626fc6895a174008e42c09-2.docx
        // f1f2ac7361626fc6895a174008e42c09-2(1).docx
        // f1f2ac7361626fc6895a174008e42c09-234.mp3
        // f1f2ac7361626fc6895a174008e42c09-234(123).3gp
        // f1f2ac7361626fc6895a174008e42c09.docx
        // f1f2ac7361626fc6895a174008e42c09(1).docx
        // f1f2ac7361626fc6895a174008e42c09.mp3
        // f1f2ac7361626fc6895a174008e42c09(123).3gp
        $regex = '/\w{32}(-\d+)?(\(\d+\))?\.\w+/';

        if (preg_match($regex, $fileid) !== 1) {
            return Response::error404('Файл не найден');
        }

        $file_path = SettingsFile::TEMP_FOLDER . $fileid;

        if (!is_readable($file_path) || !is_file($file_path)) {
            return Response::error404('Файл не найден');
        }

        if (empty($filename)) {
            $name = $fileid;
        } else {
            $fileid_ext = pathinfo($fileid, PATHINFO_EXTENSION);
            $filename_ext = pathinfo($filename, PATHINFO_EXTENSION);

            if ($fileid_ext === $filename_ext) {
                $name = $filename;
            } else {
                $name = $filename . '.' . $filename_ext;
            }
        }

        $token = (new FileToken($this->dic))->generate('temporary', $fileid, $name);
        if (empty($token)) {
            return Response::error500('Ключ для скачивания файла не был создан, попробуйте ещё раз');
        }

        return Response::data([
            'token' => $token,
        ]);
    }
}
