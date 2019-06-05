<?php
/**
 * Получим одну запись по ИД
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * @return ORM
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function get($id)
    {
        return ORM::for_table('access_sets')
            ->where_null('deleted_at')
            ->find_one($id);
    }
}
