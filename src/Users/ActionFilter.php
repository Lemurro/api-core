<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Поиск пользователей по фильтру
 */
class ActionFilter extends Action
{
    /**
     * Выполним действие
     *
     * @param array $filter
     *
     * @return array
     */
    public function run($filter)
    {
        if (empty($filter)) {
            return Response::error400('Не указаны условия для выборки');
        }

        $fields = $this->getFields();
        $sql_where = $this->getSqlWhere($filter, $fields);
        $users = $this->getInfoUsers($sql_where);

        return Response::data([
            'count' => count($users),
            'items' => $users,
        ]);
    }

    /**
     * Подготовим список имён полей для валидации
     *
     * @return array
     */
    protected function getFields()
    {
        $info_users = [];
        $users = [];

        $cols_info_users = $this->dbal->fetchAllAssociative('SHOW COLUMNS FROM info_users');
        foreach ($cols_info_users as $cols_info_user) {
            $info_users[] = $cols_info_user['Field'];
        }

        $cols_users = $this->dbal->fetchAllAssociative('SHOW COLUMNS FROM users');
        foreach ($cols_users as $cols_user) {
            $users[] = $cols_user['Field'];
        }

        return [
            'info_users' => $info_users,
            'users' => $users,
        ];
    }

    /**
     * Подготовим условие для выборки
     *
     * @param array $filter
     * @param array $fields
     *
     * @return array
     */
    protected function getSqlWhere($filter, $fields)
    {
        /*
        $filter = [
            'field_name1' => string,
            'field_name2' => string,
            ...
        ];
        */

        $query = ['1 = 1'];
        $params = [];

        $roles_type = $filter['lemurro_roles_type'];
        unset($filter['lemurro_roles_type']);

        foreach ($filter as $field => $value) {
            $value = trim($value);

            if ($value != '' && $value != 'all') {
                switch ($field) {
                    case 'lemurro_user_fio':
                        $query[] = "CONCAT(`iu`.`last_name`,' ',`iu`.`first_name`,' ',`iu`.`second_name`) LIKE ?";
                        $params[] = '%' . $value . '%';
                        break;

                    case 'lemurro_roles':
                        // $value = 'example|read'
                        $role = explode('|', $value);

                        if (is_array($role) && count($role) === 2) {
                            if ($roles_type == 0) {
                                $where_roles_type = 'IS NULL';
                            } else {
                                $where_roles_type = 'IS NOT NULL';
                            }

                            if ($role[1] === '!any!') {
                                // `iu`.`roles` = {"guide":["read"],"example":["read","create-update","delete"]}
                                // JSON_SEARCH(JSON_KEYS(`roles`), 'one', 'example') IS NOT NULL
                                $query[] = "JSON_SEARCH(JSON_KEYS(`roles`), 'one', ?) $where_roles_type";
                                $params[] = $role[0]; // example
                            } else {
                                // `iu`.`roles` = {"guide":["read"],"example":["read","create-update","delete"]}
                                // JSON_SEARCH(`roles`->>'$.example', 'one', 'read') IS NOT NULL
                                $query[] = "JSON_SEARCH(`roles`->>?, 'one', ?) $where_roles_type";
                                $params[] = '$.' . $role[0]; // example
                                $params[] = $role[1]; // read
                            }
                        }
                        break;

                    default:
                        if (in_array($field, $fields['info_users'], true)) {
                            $query[] = '`iu`.`' . $field . '` = ?';
                            $params[] = $value;
                        } else {
                            if (in_array($field, $fields['users'], true)) {
                                $query[] = '`u`.`' . $field . '` = ?';
                                $params[] = $value;
                            }
                        }
                        break;
                }
            }
        }

        return [
            'query' => implode(' AND ', $query),
            'params' => $params,
        ];
    }

    /**
     * Получим информацию о пользователях
     *
     * @param array $sql_where
     *
     * @return array
     */
    protected function getInfoUsers($sql_where)
    {
        $sql = <<<SQL
            SELECT
                `iu`.*,
                `u`.*,
                `s1`.`checked_at`
            FROM `info_users` `iu`
                LEFT JOIN `users` `u` ON `u`.`id` = `iu`.`user_id`
                LEFT JOIN `sessions` `s1` ON `s1`.`user_id` = `iu`.`user_id`
                LEFT JOIN `sessions` `s2` ON `s1`.`user_id` = `s2`.`user_id` AND `s1`.`checked_at` < `s2`.`checked_at`
            WHERE {$sql_where['query']}
                AND `iu`.`deleted_at` IS NULL
                AND `s2`.`id` IS NULL
            ORDER BY `s1`.`checked_at` DESC
            SQL;

        $users = $this->dbal->fetchAllAssociative($sql, $sql_where['params']);

        foreach ($users as &$item) {
            $item['id'] = $item['user_id'];
            $item['locked'] = ($item['locked'] === '1');
            $item['last_action_date'] = $item['checked_at'];
        }

        return $users;
    }
}
