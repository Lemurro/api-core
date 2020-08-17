<?php

/**
 * Изменение пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 17.08.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Save as RunAfterSave;
use Lemurro\Api\App\RunBefore\Users\Save as RunBeforeSave;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionSave
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionSave extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.08.2020
     */
    public function run($id, $data)
    {
        $data = (new RunBeforeSave($this->dic))->run($data);

        $check_auth_id = ORM::for_table('users')
            ->select('id')
            ->where_equal('auth_id', $data['auth_id'])
            ->where_not_equal('id', $id)
            ->where_null('deleted_at')
            ->find_one();
        if (is_object($check_auth_id)) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        $user = ORM::for_table('users')
            ->where_null('deleted_at')
            ->find_one($id);
        if (is_object($user) && $user->id == $id) {
            if ($id == 1) {
                $data['auth_id'] = 'lemurro@lemurro';
                $data['roles'] = ['admin' => 'true'];
                $data['info_users']['last_name'] = 'Пользователь';
                $data['info_users']['first_name'] = 'для';
                $data['info_users']['second_name'] = 'cli-скриптов';
            }

            $user->auth_id = $data['auth_id'];
            $user->updated_at = $this->dic['datetimenow'];
            $user->save();
            if (is_object($user) && isset($user->id)) {
                $info = ORM::for_table('info_users')
                    ->where_equal('user_id', $id)
                    ->find_one();
                if (is_object($info) && $info->user_id == $id) {
                    if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                        foreach ($data['info_users'] as $key => $value) {
                            $info[$key] = $value;
                        }
                    }

                    if (!isset($data['roles']) || !is_array($data['roles'])) {
                        $data['roles'] = [];
                    }

                    $json_roles = json_encode($data['roles']);

                    $info->roles = $json_roles;
                    $info->updated_at = $this->dic['datetimenow'];
                    $info->save();
                    if (is_object($info) && isset($info->id)) {
                        /** @var DataChangeLog $data_change_log */
                        $data_change_log = $this->dic['datachangelog'];
                        $data_change_log->insert('users', $data_change_log::ACTION_UPDATE, $id, $data);

                        $data['id'] = $id;
                        $data['locked'] = ($user->locked === '1');
                        $data['last_action_date'] = $this->getLastActionDate($id);
                        $data['roles'] = $json_roles;

                        if (isset($data['info_users']) && !empty($data['info_users'])) {
                            $data = array_merge($data, $data['info_users']);
                            unset($data['info_users']);
                        }

                        return (new RunAfterSave($this->dic))->run($data);
                    } else {
                        return Response::error500('Произошла ошибка при изменении информации пользователя, попробуйте ещё раз');
                    }
                } else {
                    return Response::error404('Информация о пользователе не найдена');
                }
            } else {
                return Response::error500('Произошла ошибка при изменении пользователя, попробуйте ещё раз');
            }
        } else {
            return Response::error404('Пользователь не найден');
        }
    }

    /**
     * Получим дату последнего действия
     *
     * @param integer $id ИД пользователя
     *
     * @return string
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.10.2019
     */
    protected function getLastActionDate($id)
    {
        $item = ORM::for_table('sessions')
            ->select('checked_at')
            ->where_equal('user_id', $id)
            ->order_by_desc('checked_at')
            ->limit(1)
            ->find_one();
        if (is_object($item) && $item->user_id == $id) {
            return $item->checked_at;
        } else {
            return null;
        }
    }
}
