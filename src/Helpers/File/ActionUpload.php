<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Helpers\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ActionUpload extends AbstractFileAction
{
    /**
     * @param FileBag $file Загруженный файл
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
     */
    public function run($file)
    {
        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $file->get('uploadfile');

        $orig_tmp = $uploaded_file->getPathname();
        $orig_mime = mime_content_type($orig_tmp);
        $orig_size = $uploaded_file->getSize();
        $orig_ext = $uploaded_file->getClientOriginalExtension();

        switch ($this->dic['config']['file']['check_file_by']) {
            case 'type':
                $type_corrected = in_array($orig_mime, $this->dic['config']['file']['allowed_types']);
                break;

            case 'ext':
                $type_corrected = in_array($orig_ext, $this->dic['config']['file']['allowed_extensions']);
                break;

            default:
                $this->log->info('File: Неверный вид проверки типа файла: ' . $this->dic['config']['file']['check_file_by']);

                return Response::error400('Неверные настройки приложения, пожалуйста обратитесь к администратору');
                break;
        }

        if ($type_corrected === false) {
            $this->log->info('File: Попытка загрузки неразрешённого файла .' . $orig_ext . ': ' . $orig_mime);

            $allowed_extensions = implode(', ', $this->dic['config']['file']['allowed_extensions']);

            return Response::error400('Разрешённые форматы: ' . $allowed_extensions, [
                'mime' => $orig_mime,
                'ext'  => $orig_ext,
            ]);
        }

        if ($orig_size > $this->dic['config']['file']['allowed_size_bytes']) {
            return Response::error400('Максимальный размер файла: ' . $this->dic['config']['file']['allowed_size_formated']);
        }

        $dest_folder = $this->dic['config']['file']['path_temp'] . '/';
        $dest_name = md5_file($orig_tmp);

        if (isset($this->dic['user']['id'])) {
            $dest_name .= '-' . $this->dic['user']['id'];
        } else {
            $dest_name .= '-' . str_replace('.', '', uniqid('', true));
        }

        $file_id = (new FileName())->generate($dest_folder, $dest_name, $orig_ext);

        $uploaded_file->move($dest_folder, $file_id);

        if (!is_readable($dest_folder . $file_id) || !is_file($dest_folder . $file_id)) {
            $this->log->error('File: Файл не был загружен', [
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
