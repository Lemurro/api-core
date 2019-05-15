<?php
/**
 * Загрузка файла во временный каталог
 *
 * @version 15.05.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Class ActionUpload
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class ActionUpload extends Action
{
    /**
     * Выполним действие
     *
     * @param FileBag $file Загруженный файл
     *
     * @return array
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($file)
    {
        /** @var Logger $log */
        $log = $this->dic['log'];

        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $file->get('uploadfile');

        $orig_tmp = $uploaded_file->getPathname();
        $orig_mime = mime_content_type($orig_tmp);
        $orig_size = $uploaded_file->getSize();
        $orig_ext = $uploaded_file->getClientOriginalExtension();

        if (in_array($orig_mime, SettingsFile::ALLOWED_TYPES)) {
            if ($orig_size <= SettingsFile::ALLOWED_SIZE) {
                $dest_folder = SettingsFile::TEMP_FOLDER;
                $dest_name = md5_file($orig_tmp);

                if (isset($this->dic['user']['id'])) {
                    $dest_name .= '-' . $this->dic['user']['id'];
                }

                $file_id = (new FileName())->generate($dest_folder, $dest_name, $orig_ext);

                $uploaded_file->move($dest_folder, $file_id);

                if (is_readable($dest_folder . $file_id) && is_file($dest_folder . $file_id)) {
                    return Response::data([
                        'id' => $file_id,
                    ]);
                } else {
                    $log->error('File: Файл не был загружен', [
                        'mime'    => $orig_mime,
                        'size'    => $orig_size,
                        'ext'     => $orig_ext,
                        'file_id' => $file_id,
                    ]);

                    return Response::error500('Файл не был загружен, попробуйте ещё раз');
                }
            } else {
                return Response::error400('Максимальный размер файла: ' . SettingsFile::MAX_SIZE_FORMATED);
            }
        } else {
            $log->info('File: Попытка загрузки неразрешённого файла .' . $orig_ext . ': ' . $orig_mime);

            $allowed_extensions = implode(', ', SettingsFile::ALLOWED_EXTENSIONS);

            return Response::error400('Разрешённые форматы: ' . $allowed_extensions, [
                'mime' => $orig_mime,
                'ext'  => $orig_ext,
            ]);
        }
    }
}
