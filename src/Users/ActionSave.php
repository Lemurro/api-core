<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Save as RunAfterSave;
use Lemurro\Api\App\RunBefore\Users\Save as RunBeforeSave;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Изменение пользователя
 */
class ActionSave extends Action
{
    /**
     * Изменение пользователя
     *
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @return array
     */
    public function run($id, $data)
    {
        $data = (new RunBeforeSave($this->dic))->run($data);

        $check_id = $this->dbal->fetchOne('SELECT id FROM users WHERE auth_id = ? AND id != ? AND deleted_at IS NULL', [$data['auth_id'], $id]);
        if ($check_id !== false && (int)$check_id === (int)$id) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        $user = $this->dbal->fetchAssociative('SELECT id, locked FROM users WHERE id = ? AND deleted_at IS NULL', [$id]);
        if ($user === false || (int)$user['id'] !== (int)$id) {
            return Response::error404('Пользователь не найден');
        }

        $userinfo_user_id = $this->dbal->fetchOne('SELECT user_id FROM info_users WHERE user_id = ?', [$id]);
        if ($userinfo_user_id === false || (int)$userinfo_user_id !== (int)$id) {
            return Response::error404('Информация о пользователе не найдена');
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        if ($id == 1) {
            $data['auth_id'] = 'lemurro@lemurro';
            $data['roles'] = ['admin' => 'true'];
            $data['info_users']['last_name'] = 'Пользователь';
            $data['info_users']['first_name'] = 'для';
            $data['info_users']['second_name'] = 'cli-скриптов';
            $data['info_users']['email'] = 'lemurro@lemurro';
        }

        $info = [];
        if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
            foreach ($data['info_users'] as $key => $value) {
                $info[$key] = $value;
            }
        }

        $json_roles = json_encode($data['roles']);

        $info['roles'] = $json_roles;
        $info['updated_at'] = $this->dic['datetimenow'];

        $this->dbal->transactional(function () use ($id, $data, $info): void {
            $this->dbal->update('users', [
                'auth_id' => $data['auth_id'],
                'updated_at' => $this->dic['datetimenow'],
            ], [
                'id' => $id
            ]);

            $this->dbal->update('info_users', $info, [
                'user_id' => $id
            ]);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', 'update', $id, $data);
        });

        $data['id'] = $id;
        $data['locked'] = (int)$user['locked'] === 1;
        $data['last_action_date'] = $this->getLastActionDate($id);
        $data['roles'] = $json_roles;

        if (isset($data['info_users']) && !empty($data['info_users'])) {
            $data = array_merge($data, $data['info_users']);
            unset($data['info_users']);
        }

        return (new RunAfterSave($this->dic))->run($data);
    }

    /**
     * Получим дату последнего действия
     *
     * @param integer $id ИД пользователя
     *
     * @return ?string
     */
    protected function getLastActionDate($id)
    {
        $checked_at = $this->dbal->fetchOne('SELECT checked_at FROM sessions WHERE user_id = ? ORDER BY checked_at DESC', [$id]);
        if ($checked_at !== false) {
            return $checked_at;
        }

        return null;
    }
}
