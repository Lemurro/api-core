<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Remove as RunAfterRemove;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Удаление пользователя
 */
class ActionRemove extends Action
{
    /**
     * Удаление пользователя
     *
     * @param integer $id ИД записи
     *
     * @return array
     */
    public function run($id)
    {
        if ($id == 1) {
            return Response::error403('Пользователь с id=1 не может быть удалён', false);
        }

        $user = $this->dbal->fetchAssociative('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$id]);
        if ($user === false || (int)$user['id'] !== (int)$id) {
            return Response::error404('Пользователь не найден');
        }

        $info = $this->dbal->fetchAssociative('SELECT * FROM info_users WHERE user_id = ?', [$id]);
        if ($info === false || (int)$info['user_id'] !== (int)$id) {
            return Response::error404('Информация о пользователе не найдена');
        }

        $this->dbal->transactional(function () use ($id): void {
            $this->dbal->update('users', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'id' => $id
            ]);

            $this->dbal->update('info_users', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'user_id' => $id
            ]);

            $this->dbal->delete('auth_codes', ['user_id' => $id]);
            $this->dbal->delete('sessions', ['user_id' => $id]);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', 'delete', $id);
        });

        return (new RunAfterRemove($this->dic))->run([
            'id' => $id,
        ]);
    }
}
