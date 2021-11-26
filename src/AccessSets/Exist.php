<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class Exist
{
    /**
     * @param integer $id   ИД записи
     * @param string  $name Строка для проверки
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public static function check($id, $name): array
    {
        $count = DB::table('access_sets')
            ->select('id')
            ->where('name', '=', $name)
            ->where('id', '<>', $id)
            ->whereNull('deleted_at')
            ->get();

        if ($count->count() > 0) {
            return Response::error400('Набор с таким именем уже существует');
        }

        return Response::data([]);
    }
}
