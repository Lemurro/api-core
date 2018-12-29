<?php
/**
 * Получение пользователя
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionGet
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionGet extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id ИД записи
     *
     * @return array
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id)
    {
        $record = ORM::for_table('info_users')
            ->table_alias('iu')
            ->left_outer_join('users', ['u.id', '=', 'iu.user_id'], 'u')
            ->where_equal('iu.user_id', $id)
            ->where_null('iu.deleted_at')
            ->find_one();
        if (is_object($record)) {
            $record = $record->as_array();

            $record['id'] = $record['user_id'];

            if ($record['roles'] != '') {
                $record['roles'] = json_decode($record['roles'], true);
            } else {
                $record['roles'] = [];
            }

            return Response::data($record);
        } else {
            return Response::error404('Пользователь не найден');
        }
    }
}
