<?php

/**
 * Заблокировать \ Разблокировать пользователя
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\App\RunAfter\Users\LockUnlock as RunAfterLockUnlock;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

class ActionLockUnlock extends Action
{
    /**
     * @param integer $id     ИД записи
     * @param boolean $locked Статус блокировки (true|false)
     */
    public function run($id, $locked): array
    {
        $user = DB::table('users')
            ->where('id', '=', $id)
            ->whereNull('deleted_at')
            ->first();

        if ($user === null) {
            return Response::error404('Пользователь не найден');
        }

        if ((int) $id === 1 && $locked) {
            return Response::error403('Пользователь с id=1 не может быть заблокирован', false);
        }

        $user_info = (new ActionGet($this->dic))->run($id);
        if (isset($user_info['errors'])) {
            return $user_info;
        }

        $affected = DB::table('users')
            ->where('id', '=', $id)
            ->update([
                'locked' => $locked,
                'updated_at' => $this->datetimenow,
            ]);

        if ($affected === 1) {
            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', $data_change_log::ACTION_UPDATE, $id, (array) $user);
        }

        $user_info['data']['locked'] = $locked;

        if ($locked) {
            DB::table('sessions')
                ->where('user_id', '=', $id)
                ->delete();
        }

        return (new RunAfterLockUnlock($this->dic))->run($user_info['data']);
    }
}
