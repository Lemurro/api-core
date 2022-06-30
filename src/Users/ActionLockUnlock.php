<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\LockUnlock as RunAfterLockUnlock;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Заблокировать \ Разблокировать пользователя
 */
class ActionLockUnlock extends Action
{
    /**
     * Заблокировать \ Разблокировать пользователя
     *
     * @param integer $id     ИД записи
     * @param boolean $locked Статус блокировки (true|false)
     *
     * @return array
     */
    public function run($id, $locked)
    {
        if ($id == 1 && $locked) {
            return Response::error403('Пользователь с id=1 не может быть заблокирован', false);
        }

        $user = $this->dbal->fetchAssociative('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$id]);
        if ($user === false) {
            return Response::error404('Пользователь не найден');
        }

        $user_info = (new ActionGet($this->dic))->run($id);
        if (isset($user_info['errors'])) {
            return $user_info;
        }

        $cnt = $this->dbal->update('users', [
            'locked' => $locked,
            'updated_at' => $this->dic['datetimenow'],
        ], [
            'id' => $id
        ]);
        if ($cnt !== 1) {
            return Response::error500('Произошла ошибка при изменении статуса блокировки пользователя, попробуйте ещё раз');
        }

        /** @var DataChangeLog $data_change_log */
        $data_change_log = $this->dic['datachangelog'];
        $data_change_log->insert('users', 'update', $id, $user);

        $user_info['data']['locked'] = $locked;

        if ($locked) {
            $this->dbal->delete('sessions', ['user_id' => $id]);
        }

        return (new RunAfterLockUnlock($this->dic))->run($user_info['data']);
    }
}
