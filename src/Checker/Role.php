<?php
/**
 * Проверка прав доступа
 *
 * @version 24.04.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Checker;

/**
 * Class Role
 *
 * @package Lemurro\Api\Core\Checker
 */
class Role
{
    /**
     * Запускаем проверку
     *
     * @param array $data       Массив данных
     * @param array $user_roles Массив ролей пользователя
     *
     * @return array
     *
     * @version 24.04.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($data, $user_roles)
    {
        if (count($user_roles) > 0) {
            if (isset($user_roles['admin'])) {
                return [];
            } else {
                if (isset($data['page']) && isset($data['access']) && $data['page'] !== '' && $data['access'] !== '') {
                    if (isset($user_roles[$data['page']]) && in_array($data['access'], $user_roles[$data['page']])) {
                        return [];
                    }
                }
            }
        }

        return [
            'errors' => [
                [
                    'status' => '403 Forbidden',
                    'code'   => 'warning',
                    'title'  => 'Доступ ограничен',
                ],
            ],
        ];
    }
}