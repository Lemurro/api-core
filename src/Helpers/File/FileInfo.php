<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileInfo
{
    /**
     * @param integer $id ИД файла
     *
     * @return object|null
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function getOne($id)
    {
        return DB::table('files')
            ->select(
                'id',
                'name',
                'ext',
                'created_at'
            )
            ->where('id', '=', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * @param array $ids ИД файлов
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function getMany($ids): Collection
    {
        return DB::table('files')
            ->select(
                'id',
                'name',
                'ext',
                'created_at'
            )
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->get();
    }
}
