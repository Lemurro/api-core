<?php
/**
 * Поиск пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 15.10.2019
 */

namespace Lemurro\Api\Core\Users;

use ORM;

/**
 * Class Find
 *
 * @package Lemurro\Api\Core\Users
 */
class Find
{
    /**
     * Найдем пользователя по идентификатору
     *
     * @param string $auth_id Номер телефона или электронная почта
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.10.2019
     */
    public function run($auth_id)
    {
        $user = ORM::for_table('users')
            ->where_equal('auth_id', $auth_id)
            ->find_one();
        if (is_object($user) && $user->auth_id == $auth_id) {
            return $user->as_array();
        } else {
            return [];
        }
    }
}
