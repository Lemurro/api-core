<?php
/**
 * Загрузка файла во временный каталог
 *
 * @version 06.06.2019
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
     * @version 06.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($file): array
    {
        /** @var Logger $log */
        $log = $this->dic['log'];

        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $file->get('uploadfile');

        $orig_tmp = $uploaded_file->getPathname();
        $orig_mime = mb_strtolower(mime_content_type($orig_tmp), 'UTF-8');
        $orig_size = $uploaded_file->getSize();
        $orig_ext = mb_strtolower($uploaded_file->getClientOriginalExtension(), 'UTF-8');

        switch (SettingsFile::CHECK_FILE_BY) {
            case 'type':
                $type_corrected = in_array($orig_mime, SettingsFile::ALLOWED_TYPES);
                break;

            case 'ext':
                $type_corrected = in_array($orig_ext, SettingsFile::ALLOWED_EXTENSIONS);
                break;

            default:
                $log->info('File: Неверный вид проверки типа файла: ' . SettingsFile::CHECK_FILE_BY);

                return Response::error400('Неверные настройки приложения, пожалуйста обратитесь к администратору');
                break;
        }

        if ($type_corrected === false) {
            $log->info('File: Попытка загрузки неразрешённого файла .' . $orig_ext . ': ' . $orig_mime);

            $allowed_extensions = implode(', ', SettingsFile::ALLOWED_EXTENSIONS);

            return Response::error400('Разрешённые форматы: ' . $allowed_extensions, [
                'mime' => $orig_mime,
                'ext'  => $orig_ext,
            ]);
        }

        if ($orig_size > SettingsFile::ALLOWED_SIZE) {
            return Response::error400('Максимальный размер файла: ' . SettingsFile::MAX_SIZE_FORMATED);
        }

        $dest_folder = SettingsFile::TEMP_FOLDER;
        $dest_name = md5_file($orig_tmp);

        if (isset($this->dic['user']['id'])) {
            $dest_name .= '-' . $this->dic['user']['id'];
        } else {
            $dest_name .= '-' . str_replace('.', '', uniqid('', true));
        }

        $file_id = (new FileName())->generate($dest_folder, $dest_name, $orig_ext);

        $uploaded_file->move($dest_folder, $file_id);

        if (!is_readable($dest_folder . $file_id) || !is_file($dest_folder . $file_id)) {
            $log->error('File: Файл не был загружен', [
                'mime'    => $orig_mime,
                'size'    => $orig_size,
                'ext'     => $orig_ext,
                'file_id' => $file_id,
            ]);

            return Response::error500('Файл не был загружен, попробуйте ещё раз');
        }

        return Response::data([
            'id' => $file_id,
        ]);
    }
}
