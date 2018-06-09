<?php
/**
 * Поиск пользователя
 *
 * @version 19.04.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

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
     * @version 19.04.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($auth_id)
    {
        $user = \ORM::for_table('users')
            ->where_equal('auth_id', $auth_id)
            ->find_one();
        if (is_object($user)) {
            return $user->as_array();
        } else {
            return [];
        }
    }
}
