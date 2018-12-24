<?php
/**
 * Изменение пользователя
 *
 * @version 24.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Save as RunAfterSave;
use Lemurro\Api\App\RunBefore\Users\Save as RunBeforeSave;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\DataChangeLogs\Insert as DataChangeLogInsert;
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
     * @version 24.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id, $data)
    {
        $data = (new RunBeforeSave($this->dic))->run($data);

        $check_auth_id = ORM::for_table('users')
            ->select('id')
            ->where_equal('auth_id', $data['auth_id'])
            ->where_not_equal('id', $id)
            ->find_one();
        if (is_object($check_auth_id)) {
            return Response::error('400 Bad Request', 'info', 'Пользователь с такими данными для входа уже существует');
        }

        $user = ORM::for_table('users')
            ->find_one($id);
        if (is_object($user)) {
            $user->auth_id = $data['auth_id'];
            $user->save();
            if (is_object($user) && isset($user->id)) {
                $info = ORM::for_table('info_users')
                    ->where_equal('user_id', $id)
                    ->find_one();
                if (is_object($info)) {
                    $result_data = [];
                    if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                        foreach ($data['info_users'] as $key => $value) {
                            $result_data[$key] = $value;
                            $info[$key] = $value;
                        }
                    }

                    if (!isset($data['roles']) || !is_array($data['roles'])) {
                        $data['roles'] = [];
                    }

                    $info->roles = json_encode($data['roles']);
                    $info->updated_at = $this->dic['datetimenow'];
                    $info->save();
                    if (is_object($info) && isset($info->id)) {
                        /** @var DataChangeLogInsert $datachangelog */
                        $datachangelog = $this->dic['datachangelog'];
                        $datachangelog->insert('users', 'update', $id, $data);

                        $result_data['id'] = $id;
                        $result_data['auth_id'] = $data['auth_id'];
                        $result_data['last_action_date'] = $this->getLastActionDate($id);

                        return (new RunAfterSave($this->dic))->run($result_data);
                    } else {
                        return Response::error(
                            '500 Internal Server Error',
                            'danger',
                            'Произошла ошибка при изменении информации пользователя, попробуйте ещё раз'
                        );
                    }
                } else {
                    return Response::error(
                        '404 Not Found',
                        'info',
                        'Информация о пользователе не найдена'
                    );
                }
            } else {
                return Response::error(
                    '500 Internal Server Error',
                    'danger',
                    'Произошла ошибка при изменении пользователя, попробуйте ещё раз'
                );
            }
        } else {
            return Response::error(
                '404 Not Found',
                'info',
                'Пользователь не найден'
            );
        }
    }

    /**
     * Получим дату последнего действия
     *
     * @param integer $id ИД пользователя
     *
     * @return string
     *
     * @version 12.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function getLastActionDate($id)
    {
        $item = ORM::for_table('sessions')
            ->select('checked_at')
            ->where_equal('user_id', $id)
            ->order_by_desc('checked_at')
            ->limit(1)
            ->find_one();
        if (is_object($item)) {
            return $item->checked_at;
        } else {
            return 'отсутствует';
        }
    }
}
