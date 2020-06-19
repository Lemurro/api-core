<?php

/**
 * Манипуляции с файлами (добавление и удаление)
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class FileManipulate
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileManipulate extends Action
{
    /**
     * Выполним действие
     *
     * @param array  $files          Массив файлов
     * @param string $container_type Тип контейнера
     * @param string $container_id   ИД контейнера
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function run($files, $container_type = 'default', $container_id = null)
    {
        $file_add = new FileAdd($this->dic);
        $file_remove = new FileRemove($this->dic);

        $files_ids = [];
        $files_errors = [];

        if (!empty($files)) {
            foreach ($files as $file) {
                switch ($file['action']) {
                    case 'add':
                        $result = $file_add->run(
                            $file['file_id'],
                            $file['orig_name'],
                            $container_type,
                            $container_id
                        );

                        if (!$result['success']) {
                            $files_errors[] = array_merge($file, $result);
                        } else {
                            $files_ids[] = $result['data']['id'];
                        }
                        break;

                    case 'remove':
                        $result = $file_remove->run($file['file_id']);

                        if (!$result['success']) {
                            $files_ids[] = $file['file_id'];
                            $files_errors[] = array_merge($file, $result);
                        }
                        break;

                    default:
                        $files_ids[] = $file['file_id'];
                        break;
                }
            }
        }

        return [
            'ids' => $files_ids,
            'errors' => $files_errors,
            'class_file_add' => $file_add,
        ];
    }
}
