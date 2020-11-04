<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\App\RunAfter\Users\Remove as RunAfterRemove;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Throwable;

/**
 * @package Lemurro\Api\Core\Users
 */
class ActionRemove extends Action
{
    /**
     * @param integer $id ИД записи
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($id): array
    {
        if ((int) $id === 1) {
            return Response::error403('Пользователь с id=1 не может быть удалён', false);
        }

        try {
            DB::beginTransaction();

            DB::table('users')
                ->where('id', '=', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $this->datetimenow,
                ]);

            DB::table('info_users')
                ->where('user_id', '=', $id)
                ->update([
                    'deleted_at' => $this->datetimenow,
                ]);

            DB::table('auth_codes')
                ->where('user_id', '=', $id)
                ->delete();

            DB::table('sessions')
                ->where('user_id', '=', $id)
                ->delete();

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', $data_change_log::ACTION_DELETE, $id);

            DB::commit();

            return (new RunAfterRemove($this->dic))->run([
                'id' => $id,
            ]);
        } catch (Throwable $th) {
            DB::rollBack();

            LogException::write($this->dic['log'], $th);

            return Response::error500('Произошла ошибка при удалении пользователя, попробуйте ещё раз');
        }
    }
}
