<?php

/**
 * Переносим файл в постоянное хранилище и добавляем в базу
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 23.12.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Monolog\Logger;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileAdd extends Action
{
    protected Logger $log;
    protected array $undo_list = [];

    /**
     * @param string $file_name      Имя файла во временном каталоге
     * @param string $orig_name      Оригинальное имя файла
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.01.2020
     */
    public function run($file_name, $orig_name, $container_type = 'default', $container_id = null)
    {
        $this->log = $this->dic['log'];

        $move_result = $this->moveToStorage($file_name);
        if (isset($move_result['errors'])) {
            return $move_result;
        }

        return $this->addToDB(
            $move_result['data']['file_name'],
            $orig_name,
            $container_type,
            $container_id
        );
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function undo(): bool
    {
        if (!empty($this->undo_list)) {
            foreach ($this->undo_list as $item) {
                rename($item['destination_file'], $item['source_file']);

                if (isset($item['id'])) {
                    DB::table('files')
                        ->where('id', '=', $item['id'])
                        ->delete();
                }
            }
        }

        return true;
    }

    /**
     * @param string $source_file_name Имя файла во временном каталоге
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    protected function moveToStorage($source_file_name)
    {
        $source_file = $this->dic['config']['file']['path_temp'] . '/' . $source_file_name;

        if (!is_file($source_file) || !is_readable($source_file)) {
            $this->log->error('File: Файл отсутствует или не может быть прочитан', [
                'source_file_name' => $source_file_name,
            ]);

            return Response::error500('Файл отсутствует или не может быть прочитан, попробуйте загрузить файл снова', [
                'source_file_name' => $source_file_name,
            ]);
        }

        $info = pathinfo($source_file_name);

        $md5 = md5_file($source_file);
        $first_folder = substr($md5, 0, 2);
        $second_folder = substr($md5, 2, 2);
        $suffix_folder = $first_folder . '/' . $second_folder;

        $dest_folder = $this->dic['config']['file']['path_upload'] . '/' . $suffix_folder;

        if (!is_dir($dest_folder) && !mkdir($dest_folder, 0755, true) && !is_dir($dest_folder)) {
            return Response::error500('Каталог "' . $dest_folder . '" не был создан, обратитесь к разработчику');
        }

        $file_name = (new FileName())->generate($dest_folder, $info['filename'], $info['extension']);

        $path_file = $suffix_folder . '/' . $file_name;
        $destination_file = $dest_folder . '/' . $file_name;

        if (!rename($source_file, $destination_file)) {
            $this->log->error('File: Файл не был перемещён', [
                'source_file_name' => $source_file_name,
                'file_name'        => $path_file,
            ]);

            return Response::error500('Файл не был перемещён, попробуйте загрузить файл снова', [
                'source_file_name' => $source_file_name,
            ]);
        }

        $this->undo_list[$path_file] = [
            'source_file'      => $source_file,
            'destination_file' => $destination_file,
        ];

        return Response::data([
            'file_name' => $path_file,
        ]);
    }

    /**
     * @param string $file_name      Имя файла в постоянном хранилище
     * @param string $orig_name      Оригинальное имя файла
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 23.12.2020
     */
    protected function addToDB($file_name, $orig_name, $container_type, $container_id): array
    {
        $data = [
            'path'           => $file_name,
            'name'           => pathinfo($orig_name, PATHINFO_FILENAME),
            'ext'            => pathinfo($file_name, PATHINFO_EXTENSION),
            'container_type' => $container_type,
            'container_id'   => $container_id,
        ];

        if (empty($data['name'])) {
            $data['name'] = 'file';
        }

        if (empty($data['ext'])) {
            $data['ext'] = 'ext';
        }

        $file_id = DB::table('files')->insertGetId([
            'path' => $data['path'],
            'name' => mb_substr($data['name'], 0, 255, 'UTF-8'),
            'ext' => $data['ext'],
            'container_type' => $container_type,
            'container_id' => $container_id,
            'created_at' => $this->datetimenow,
        ]);

        /** @var DataChangeLog $datachangelog */
        $datachangelog = $this->dic['datachangelog'];
        $datachangelog->insert('files', $datachangelog::ACTION_INSERT, $file_id, $data);

        if (isset($this->undo_list[$file_name])) {
            $this->undo_list[$file_name]['id'] = $file_id;
        }

        return Response::data([
            'id' => $file_id,
        ]);
    }
}
