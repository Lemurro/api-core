<?php
/**
 * Добавление пользователя
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Insert as RunAfterInsert;
use Lemurro\Api\App\RunBefore\Users\Insert as RunBeforeInsert;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\DataChangeLog\DataChangeLog;
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($data)
    {
        $data = (new RunBeforeInsert($this->dic))->run($data);

        $check_auth_id = ORM::for_table('users')
            ->select('id')
            ->where_equal('auth_id', $data['auth_id'])
            ->find_one();
        if (is_object($check_auth_id)) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        $new_user = ORM::for_table('users')->create();
        $new_user->auth_id = $data['auth_id'];
        $new_user->created_at = $this->dic['datetimenow'];
        $new_user->save();
        if (is_object($new_user) && isset($new_user->id)) {
            $new_user_info = ORM::for_table('info_users')->create();

            $result_data = [];
            if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                foreach ($data['info_users'] as $key => $value) {
                    $result_data[$key] = $value;
                    $new_user_info[$key] = $value;
                }
            }

            if (!is_array($data['roles'])) {
                $data['roles'] = [];
            }

            $new_user_info->user_id = $new_user->id;
            $new_user_info->roles = json_encode($data['roles']);
            $new_user_info->created_at = $this->dic['datetimenow'];
            $new_user_info->save();
            if (is_object($new_user_info) && isset($new_user_info->id)) {
                /** @var DataChangeLog $data_change_log */
                $data_change_log = $this->dic['datachangelog'];
                $data_change_log->insert('users', 'insert', $new_user->id, $data);

                $result_data['id'] = $new_user->id;
                $result_data['auth_id'] = $data['auth_id'];
                $result_data['last_action_date'] = 'отсутствует';

                return (new RunAfterInsert($this->dic))->run($result_data);
            } else {
                return Response::error500('Произошла ошибка при добавлении информации о пользователе, попробуйте ещё раз');
            }
        } else {
            return Response::error500('Произошла ошибка при добавлении пользователя, попробуйте ещё раз');
        }
    }
}
