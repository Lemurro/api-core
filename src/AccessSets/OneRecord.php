<?php
/**
 * Получим одну запись по ИД
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 15.10.2019
 */

namespace Lemurro\Api\Core\AccessSets;

use ORM;

/**
 * Class OneRecord
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class OneRecord
{
    /**
     * Выполним действие
     *
     * @param integer $id ИД записи
     *
     * @return ORM|false
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.10.2019
     */
    static function get($id)
    {
        $record = ORM::for_table('access_sets')
            ->where_null('deleted_at')
            ->find_one($id);

        if (!is_object($record) || $record->id != $id) {
            return false;
        }

        return $record;
    }
}
