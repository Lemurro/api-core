<?php

/**
 * Переносим файл в постоянное хранилище и добавляем в базу
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Monolog\Logger;
use ORM;

/**
 * Class FileAdd
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileAdd extends Action
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var array
     */
    protected $undo_list = [];

    /**
     * Выполним действие
     *
     * @param string $file_name      Имя файла во временном каталоге
     * @param string $orig_name      Оригинальное имя файла
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function run($file_name, $orig_name, $container_type = 'default', $container_id = null)
    {
        $this->log = $this->dic['log'];

        $move_result = $this->moveToStorage($file_name);
        if (!$move_result['success']) {
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
     * Возвращаем файлы обратно во временное хранилище
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.10.2019
     */
    public function undo()
    {
        if (!empty($this->undo_list)) {
            foreach ($this->undo_list as $path_file => $item) {
                rename($item['destination_file'], $item['source_file']);

                if (isset($item['id'])) {
                    $file = ORM::for_table('files')
                        ->find_one($item['id']);
                    if (is_object($file) && $file->id == $item['id']) {
                        $file->delete();
                    }
                }
            }
        }

        return true;
    }

    /**
     * Переносим файл из временного каталога в хранилище
     *
     * @param string $source_file_name Имя файла во временном каталоге
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.01.2020
     */
    protected function moveToStorage($source_file_name)
    {
        $source_file = SettingsFile::TEMP_FOLDER . $source_file_name;

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
        $suffix_folder = $first_folder . '/' . $second_folder . '/';

        $dest_folder = SettingsFile::FILE_FOLDER . $suffix_folder;

        if (!is_dir($dest_folder) && !mkdir($dest_folder, 0755, true) && !is_dir($dest_folder)) {
            return Response::error500('Каталог "' . $dest_folder . '" не был создан, обратитесь к разработчику');
        }

        $file_name = (new FileName())->generate($dest_folder, $info['filename'], $info['extension']);

        $path_file = $suffix_folder . $file_name;

        if (!rename($source_file, $dest_folder . $file_name)) {
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
            'destination_file' => $dest_folder . $file_name,
        ];

        return Response::data([
            'file_name' => $path_file,
        ]);
    }

    /**
     * Переносим файл из временного каталога в хранилище
     *
     * @param string $file_name      Имя файла в постоянном хранилище
     * @param string $orig_name      Оригинальное имя файла
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.01.2020
     */
    protected function addToDB($file_name, $orig_name, $container_type, $container_id)
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

        $item = ORM::for_table('files')->create();
        $item->path = $data['path'];
        $item->name = mb_substr($data['name'], 0, 255, 'UTF-8');
        $item->ext = $data['ext'];
        $item->container_type = mb_substr($container_type, 0, 255, 'UTF-8');
        $item->container_id = $container_id;
        $item->created_at = $this->dic['datetimenow'];
        $item->save();

        if (!is_object($item) || !isset($item->id)) {
            $this->log->error('File: Файл не был добавлен', $data);

            return Response::error500('Файл не был добавлен, попробуйте загрузить файл снова', [
                'file_name'      => $file_name,
                'orig_name'      => $orig_name,
                'container_type' => $container_type,
                'container_id'   => $container_id,
            ]);
        }

        /** @var DataChangeLog $datachangelog */
        $datachangelog = $this->dic['datachangelog'];
        $datachangelog->insert('files', 'insert', $item->id, $data);

        if (isset($this->undo_list[$file_name])) {
            $this->undo_list[$file_name]['id'] = $item->id;
        }

        return Response::data([
            'id' => $item->id,
        ]);
    }
}
