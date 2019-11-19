<?php
/**
 * Добавление пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Insert as RunAfterInsert;
use Lemurro\Api\App\RunBefore\Users\Insert as RunBeforeInsert;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionInsert
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionInsert extends Action
{
    /**
     * Выполним действие
     *
     * @param array $data Массив данных
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function run($data)
    {
        $data = (new RunBeforeInsert($this->dic))->run($data);

        $check_auth_id = ORM::for_table('users')
            ->select('id')
            ->where_equal('auth_id', $data['auth_id'])
            ->where_null('deleted_at')
            ->find_one();
        if (is_object($check_auth_id)) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        $new_user = ORM::for_table('users')->create();
        $new_user->auth_id = $data['auth_id'];
        $new_user->created_at = $this->date_time_now;
        $new_user->save();
        if (is_object($new_user) && isset($new_user->id)) {
            $new_user_info = ORM::for_table('info_users')->create();

            if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                foreach ($data['info_users'] as $key => $value) {
                    $new_user_info[$key] = $value;
                }
            }

            if (!is_array($data['roles'])) {
                $data['roles'] = [];
            }

            $json_roles = json_encode($data['roles']);

            $new_user_info->user_id = $new_user->id;
            $new_user_info->roles = $json_roles;
            $new_user_info->created_at = $this->date_time_now;
            $new_user_info->save();
            if (is_object($new_user_info) && isset($new_user_info->id)) {
                $this->data_change_log->insert('users', 'insert', $new_user->id, $data);

                $data['id'] = $new_user->id;
                $data['locked'] = false;
                $data['last_action_date'] = null;
                $data['roles'] = $json_roles;

                if (isset($data['info_users']) && !empty($data['info_users'])) {
                    $data = array_merge($data, $data['info_users']);
                    unset($data['info_users']);
                }

                return (new RunAfterInsert($this->dic))->run($data);
            } else {
                return Response::error500('Произошла ошибка при добавлении информации о пользователе, попробуйте ещё раз');
            }
        } else {
            return Response::error500('Произошла ошибка при добавлении пользователя, попробуйте ещё раз');
        }
    }
}
