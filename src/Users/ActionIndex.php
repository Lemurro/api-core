<?php
/**
 * Список пользователей
 *
 * @version 19.04.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class ActionIndex
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionIndex extends Action
{
    /**
     * Выполним действие
     *
     * @return array
     *
     * @version 19.04.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run()
    {
        $users = \ORM::for_table('info_users')
            ->table_alias('iu')
            ->left_outer_join('users', ['u.id', '=', 'iu.user_id'], 'u')
            ->where_null('iu.deleted_at')
            ->order_by_asc('u.auth_id')
            ->find_array();
        if (is_array($users)) {
            $count_users = count($users);

            if ($count_users > 0) {
                foreach ($users as &$item) {
                    $item['id'] = $item['user_id'];
                }
            }

            return [
                'data' => [
                    'count' => $count_users,
                    'items' => $users,
                ],
            ];
        } else {
            return [
                'data' => [
                    'count' => 0,
                    'items' => [],
                ],
            ];
        }
    }
}
