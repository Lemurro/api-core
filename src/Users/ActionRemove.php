<?php

namespace Lemurro\Api\Core\Users;

use Doctrine\DBAL\Connection;
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

        $user = $this->dbal->fetchAssociative('SELECT * FROM users WHERE id = ? deleted_at IS NULL', [$id]);
        if ($user === false || $user['id'] !== $id) {
            return Response::error404('Пользователь не найден');
        }

        $info = $this->dbal->fetchAssociative('SELECT * FROM info_users WHERE user_id = ?', [$id]);
        if ($info === false || $info['user_id'] !== $id) {
            return Response::error404('Информация о пользователе не найдена');
        }

        $this->dbal->transactional(function (Connection $dbal) use ($id): void {
            $dbal->update('users', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'id' => $id
            ]);

            $dbal->update('info_users', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'user_id' => $id
            ]);

            $dbal->delete('auth_codes', ['user_id' => $id]);
            $dbal->delete('sessions', ['user_id' => $id]);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', 'delete', $id);
        });

        return (new RunAfterRemove($this->dic))->run([
            'id' => $id,
        ]);
    }
}
