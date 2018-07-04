<?php
/**
 * Изменение пользователя
 *
 * @version 04.07.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Save as RunAfterSave;
use Lemurro\Api\App\RunBefore\Users\Save as RunBeforeSave;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\DataChangeLogs\Insert as DataChangeLogInsert;

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
     * @version 04.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id, $data)
    {
        $data = (new RunBeforeSave($this->dic))->run($data);

        $check_auth_id = \ORM::for_table('users')
            ->select('id')
            ->where_equal('auth_id', $data['auth_id'])
            ->where_not_equal('id', $id)
            ->find_one();
        if (is_object($check_auth_id)) {
            return [
                'errors' => [
                    [
                        'status' => '400 Bad Request',
                        'code'   => 'info',
                        'title'  => 'Пользователь с такими данными для входа уже существует.',
                    ],
                ],
            ];
        }

        $user = \ORM::for_table('users')
            ->find_one($id);
        if (is_object($user)) {
            $user->auth_id = $data['auth_id'];
            $user->save();
            if (is_object($user) && isset($user->id)) {
                $info = \ORM::for_table('info_users')
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

                        return (new RunAfterSave($this->dic))->run($result_data);
                    } else {
                        return [
                            'errors' => [
                                [
                                    'status' => '500 Internal Server Error',
                                    'code'   => 'danger',
                                    'title'  => 'Произошла ошибка при изменении информации пользователя. Попробуйте ещё раз.',
                                ],
                            ],
                        ];
                    }
                } else {
                    return [
                        'errors' => [
                            [
                                'status' => '404 Not Found',
                                'code'   => 'info',
                                'title'  => 'Информация о пользователе не найдена.',
                            ],
                        ],
                    ];
                }
            } else {
                return [
                    'errors' => [
                        [
                            'status' => '500 Internal Server Error',
                            'code'   => 'danger',
                            'title'  => 'Произошла ошибка при изменении пользователя. Попробуйте ещё раз.',
                        ],
                    ],
                ];
            }
        } else {
            return [
                'errors' => [
                    [
                        'status' => '404 Not Found',
                        'code'   => 'info',
                        'title'  => 'Пользователь не найден.',
                    ],
                ],
            ];
        }
    }
}
