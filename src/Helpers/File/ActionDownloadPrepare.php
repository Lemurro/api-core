<?php

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Подготовка файла к скачиванию
 */
class ActionDownloadPrepare extends Action
{
    /**
     * Подготовка файла к скачиванию
     *
     * @param integer|string $fileid   ИД постоянного файла или имя временого файла
     * @param string         $filename Имя файла (для браузера)
     *
     * @return array
     */
    public function run($fileid, $filename = '')
    {
        if (preg_match('/^\d+$/', $fileid)) {
            return $this->permanentFile($fileid, $filename);
        }

        return $this->temporaryFile($fileid, $filename);
    }

    /**
     * Постоянный файл
     *
     * @param integer $fileid   ИД постоянного файла
     * @param string  $filename Имя файла (для браузера)
     *
     * @return array
     */
    protected function permanentFile($fileid, $filename)
    {
        $info = (new FileInfo($this->dbal))->getById($fileid);
        if (empty($info)) {
            return Response::error404('Файл не найден');
        }

        if ((new FileRights($this->dic))->check($info['container_type'], $info['container_id']) === false) {
            return Response::error403('Доступ ограничен', false);
        }

        $file_path = SettingsFile::FILE_FOLDER . $info['path'];

        if (!is_readable($file_path) || !is_file($file_path)) {
            return Response::error404('Файл не найден');
        }

        $name = $this->getFilename($info['name'] . '.' . $info['ext'], $filename);

        $token = (new FileToken($this->dic))->generate('permanent', $info['path'], $name);
        if (empty($token)) {
            return Response::error500('Ключ для скачивания файла не был создан, попробуйте ещё раз');
        }

        return Response::data([
            'token' => $token,
        ]);
    }

    /**
     * Временный файл
     *
     * @param string $fileid   Имя временого файла
     * @param string $filename Имя файла (для браузера)
     *
     * @return array
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

        $name = $this->getFilename($fileid, $filename);

        $token = (new FileToken($this->dic))->generate('temporary', $fileid, $name);
        if (empty($token)) {
            return Response::error500('Ключ для скачивания файла не был создан, попробуйте ещё раз');
        }

        return Response::data([
            'token' => $token,
        ]);
    }

    /**
     * Определим имя файла
     *
     * @param string $orig_filename Оригинальное имя файла
     * @param string $new_filename  Имя файла (для браузера)
     *
     * @return string
     */
    private function getFilename($orig_filename, $new_filename)
    {
        if (!empty($new_filename)) {
            $name = pathinfo($new_filename, PATHINFO_FILENAME);
        } else {
            $name = pathinfo($orig_filename, PATHINFO_FILENAME);
        }

        $name = FileNameCleaner::clean($name);
        $ext = pathinfo($orig_filename, PATHINFO_EXTENSION);

        return $name . '.' . $ext;
    }
}
