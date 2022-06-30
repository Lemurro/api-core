<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Insert as RunAfterInsert;
use Lemurro\Api\App\RunBefore\Users\Insert as RunBeforeInsert;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use RuntimeException;

/**
 * Добавление пользователя
 */
class ActionInsert extends Action
{
    /**
     * Добавление пользователя
     *
     * @param array $data Массив данных
     *
     * @return array
     */
    public function run($data)
    {
        $data = (new RunBeforeInsert($this->dic))->run($data);

        $auth_id = $this->dbal->fetchOne('SELECT auth_id FROM users WHERE auth_id = ? AND deleted_at IS NULL', [$data['auth_id']]);
        if ($auth_id !== false && $auth_id === (string)$data['auth_id']) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        if (!is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $json_roles = json_encode($data['roles']);

        $new_user_info = [];
        if (isset($data['info_users']) && is_array($data['info_users'])) {
            foreach ($data['info_users'] as $key => $value) {
                $new_user_info[$key] = $value;
            }
        }
        $new_user_info['roles'] = $json_roles;
        $new_user_info['created_at'] = $this->dic['datetimenow'];

        $user_id = $this->dbal->transactional(function() use ($auth_id, $new_user_info): int {
            $cnt = $this->dbal->insert('users', [
                'auth_id' => $auth_id,
                'created_at' => $this->dic['datetimenow'],
            ]);
            if ($cnt !== 1) {
                throw new RuntimeException('Произошла ошибка при добавлении пользователя, попробуйте ещё раз', 500);
            }

            $user_id = (int)$this->dbal->lastInsertId();

            $new_user_info['user_id'] = $user_id;

            $cnt = $this->dbal->insert('info_users', $new_user_info);
            if ($cnt !== 1) {
                throw new RuntimeException('Произошла ошибка при добавлении пользователя, попробуйте ещё раз', 500);
            }

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', 'insert', $user_id, [
                'auth_id' => $auth_id,
                'user_id' => $user_id,
                'user_info' => $new_user_info,
            ]);

            return $user_id;
        });

        if (isset($data['info_users']) && !empty($data['info_users'])) {
            $data = array_merge($data, $data['info_users']);
            unset($data['info_users']);
        }

        $data['id'] = $user_id;
        $data['locked'] = false;
        $data['last_action_date'] = null;
        $data['roles'] = $json_roles;

        return (new RunAfterInsert($this->dic))->run($data);
    }
}
