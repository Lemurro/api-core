<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Illuminate\Support\Facades\DB;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class OneRecord
{
    /**
     * @param integer $id ИД записи
     *
     * @return object|null
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public static function get($id)
    {
        return DB::table('access_sets')
            ->where('id', '=', $id)
            ->whereNull('deleted_at')
            ->first();
    }
}
