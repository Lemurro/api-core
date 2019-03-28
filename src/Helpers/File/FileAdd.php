<?php
/**
 * Переносим файл в постоянное хранилище и добавляем в базу
 *
 * @version 28.03.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * Выполним действие
     *
     * @param string $file_name      Имя файла во временном каталоге
     * @param string $orig_name      Оригинальное имя файла
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($file_name, $orig_name, $container_type = 'default', $container_id = null)
    {
        $this->log = $this->dic['log'];

        $move_result = $this->moveToStorage($file_name);
        if (isset($move_result['errors'])) {
            return $move_result;
        } else {
            return $this->addToDB(
                $move_result['data']['file_name'],
                $orig_name,
                $container_type,
                $container_id
            );
        }
    }

    /**
     * Переносим файл из временного каталога в хранилище
     *
     * @param string $source_file_name Имя файла во временном каталоге
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function moveToStorage($source_file_name)
    {
        $source_file = SettingsFile::TEMP_FOLDER . $source_file_name;

        $info = pathinfo($source_file_name);

        $md5 = md5_file($source_file);
        $first_folder = substr($md5, 0, 2);
        $second_folder = substr($md5, 2, 2);
        $suffix_folder = $first_folder . '/' . $second_folder . '/';

        $dest_folder = SettingsFile::FILE_FOLDER . $suffix_folder;

        if (!is_dir($dest_folder)) {
            mkdir($dest_folder, 0755, true);
        }

        $file_name = (new FileName())->generate($dest_folder, $info['filename'], $info['extension']);

        $path_file = $suffix_folder . $file_name;

        if (rename($source_file, $dest_folder . $file_name)) {
            return Response::data([
                'file_name' => $path_file,
            ]);
        } else {
            $this->log->error('File: Файл не был перемещён', [
                'source_file_name' => $source_file_name,
                'file_name'        => $path_file,
            ]);

            return Response::error500('Файл не был перемещён, попробуйте ещё раз', [
                'source_file_name' => $source_file_name,
            ]);
        }
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
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function addToDB($file_name, $orig_name, $container_type, $container_id)
    {
        $pathinfo = pathinfo($orig_name);

        $data = [
            'path'           => $file_name,
            'name'           => $pathinfo['filename'],
            'ext'            => $pathinfo['extension'],
            'container_type' => $container_type,
            'container_id'   => $container_id,
        ];

        $item = ORM::for_table('files')->create();
        $item->path = $data['path'];
        $item->name = $data['name'];
        $item->ext = $data['ext'];
        $item->container_type = $container_type;
        $item->container_id = $container_id;
        $item->created_at = $this->dic['datetimenow'];
        $item->save();
        if (is_object($item) && isset($item->id)) {
            /** @var DataChangeLog $datachangelog */
            $datachangelog = $this->dic['datachangelog'];
            $datachangelog->insert('files', 'insert', $item->id, $data);

            return Response::data([
                'id' => $item->id,
            ]);
        } else {
            $this->log->error('File: Файл не был добавлен', $data);

            return Response::error500('Файл не был добавлен, попробуйте ещё раз', [
                'file_name'      => $file_name,
                'orig_name'      => $orig_name,
                'container_type' => $container_type,
                'container_id'   => $container_id,
            ]);
        }
    }
}
