<?php
/**
 * Получим информацию по одному или нескольким файлам
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class FileInfo
 *
 * @package Lemurro\Api\Core\File
 */
class FileInfo
{
    /**
     * Получим информацию по одному файлу
     *
     * @param integer $id ИД файла
     *
     * @return ORM|array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getOneORM($id)
    {
        $info = ORM::for_table('files')
            ->where_null('deleted_at')
            ->find_one($id);
        if (is_object($info)) {
            return $info;
        } else {
            return Response::error404('Файл не найден');
        }
    }

    /**
     * Получим информацию по одному файлу
     *
     * @param integer $id ИД файла
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getOne($id)
    {
        $info = ORM::for_table('files')
            ->select_many(
                'id',
                'name',
                'ext',
                'created_at'
            )
            ->where_null('deleted_at')
            ->find_one($id);
        if (is_object($info)) {
            return Response::data($info->as_array());
        } else {
            return Response::error404('Файл не найден');
        }
    }

    /**
     * Получим информацию по нескольким файлам
     *
     * @param array $ids ИД файлов
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getMany($ids)
    {
        $info = ORM::for_table('files')
            ->select_many(
                'id',
                'name',
                'ext',
                'created_at'
            )
            ->where_id_in($ids)
            ->where_null('deleted_at')
            ->find_array();
        if (is_array($info)) {
            return Response::data($info);
        } else {
            return Response::error500('Произошла ошибка при получении данных');
        }
    }
}
