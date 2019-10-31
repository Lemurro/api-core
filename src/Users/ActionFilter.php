<?php
/**
 * Поиск пользователей по фильтру
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 27.09.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionFilter
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionFilter extends Action
{
    /**
     * @var integer Лимит пользователей при входе в раздел
     */
    protected $default_limit = 50;

    /**
     * Выполним действие
     *
     * @param array $filter
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 27.09.2019
     */
    public function run($filter)
    {
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
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 27.09.2019
     */
    protected function getFields()
    {
        $info_users = [];
        $users = [];

        $cols_info_users = ORM::for_table('info_users')
            ->raw_query('SHOW COLUMNS FROM `info_users`')
            ->find_many();
        if (is_array($cols_info_users)) {
            foreach ($cols_info_users as $cols_info_user) {
                $info_users[] = $cols_info_user->Field;
            }
        }

        $cols_users = ORM::for_table('users')
            ->raw_query('SHOW COLUMNS FROM `users`')
            ->find_many();
        if (is_array($cols_users)) {
            foreach ($cols_users as $cols_user) {
                $users[] = $cols_user->Field;
            }
        }

        return [
            'info_users' => $info_users,
            'users'      => $users,
        ];
    }

    /**
     * Подготовим условие для выборки
     *
     * @param array $filter
     * @param array $fields
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 27.09.2019
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

        $query = '1=1';
        $params = [];
        $limit = $this->default_limit;

        if (!empty($filter)) {
            $roles_type = $filter['lemurro_roles_type'];
            unset($filter['lemurro_roles_type']);

            foreach ($filter as $field => $value) {
                $value = trim($value);

                if ($value != '' && $value != 'all') {
                    switch ($field) {
                        case 'lemurro_user_fio':
                            $query .= " AND CONCAT(`iu`.`last_name`,' ',`iu`.`first_name`,' ',`iu`.`second_name`) LIKE ?";
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

                                // `iu`.`roles` = {"guide":["read"],"example":["read","create-update","delete"]}
                                // JSON_SEARCH(`roles`->>'$.example', 'one', 'read') IS NOT NULL
                                $query .= " AND JSON_SEARCH(`roles`->>?, 'one', ?) $where_roles_type";
                                $params[] = '$.' . $role[0]; // example
                                $params[] = $role[1]; // read
                            }
                            break;

                        default:
                            if (in_array($field, $fields['info_users'], true)) {
                                $query .= ' AND `iu`.`' . $field . '` = ?';
                                $params[] = $value;
                            } else {
                                if (in_array($field, $fields['users'], true)) {
                                    $query .= ' AND `u`.`' . $field . '` = ?';
                                    $params[] = $value;
                                }
                            }
                            break;
                    }
                }
            }

            // Снимем лимит на количество строк, если был использован фильтр
            if ($query != '1=1') {
                $limit = null;
            }
        }

        return [
            'query'  => $query,
            'params' => $params,
            'limit'  => $limit,
        ];
    }

    /**
     * Получим информацию о пользователях
     *
     * @param array $sql_where
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 27.09.2019
     */
    protected function getInfoUsers($sql_where)
    {
        $users = ORM::for_table('info_users')
            ->table_alias('iu')
            ->select_many(
                'iu.*',
                'u.*',
                's.checked_at'
            )
            ->left_outer_join('users', ['u.id', '=', 'iu.user_id'], 'u')
            ->left_outer_join('sessions', ['s.user_id', '=', 'iu.user_id'], 's')
            ->where_raw($sql_where['query'], $sql_where['params'])
            ->where_null('iu.deleted_at')
            ->order_by_desc('s.checked_at')
            ->limit($sql_where['limit'])
            ->find_array();

        if (!is_array($users)) {
            return [];
        }

        if ($users > 0) {
            foreach ($users as &$item) {
                $item['id'] = $item['user_id'];
                $item['locked'] = ($item['locked'] === '1');
                $item['last_action_date'] = $item['checked_at'];
            }
        }

        return $users;
    }
}
