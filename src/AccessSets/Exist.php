<?php

/**
 * Проверка на наличие подобной записи
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class Exist
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class Exist
{
    /**
     * Выполним действие
     *
     * @param integer $id   ИД записи
     * @param string  $name Строка для проверки
     *
     * @return array
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public static function check($id, $name)
    {
        $exist = ORM::for_table('access_sets')
            ->select('id')
            ->where_equal('name', $name)
            ->where_not_equal('id', $id)
            ->where_null('deleted_at')
            ->find_one();

        if (is_object($exist)) {
            return Response::error400('Набор с таким именем уже существует');
        }

        return Response::data([]);
    }
}
