<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\App\RunAfter\Users\Save as RunAfterSave;
use Lemurro\Api\App\RunBefore\Users\Save as RunBeforeSave;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Throwable;

/**
 * @package Lemurro\Api\Core\Users
 */
class ActionSave extends Action
{
    /**
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($id, $data): array
    {
        $data = (new RunBeforeSave($this->dic))->run($data);

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $check_auth_id = DB::table('users')
            ->select('id')
            ->where('auth_id', '=', $data['auth_id'])
            ->where('id', '<>', $id)
            ->whereNull('deleted_at')
            ->get();
        if ($check_auth_id->count() > 0) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        $user = DB::table('users')
            ->where('id', '=', $id)
            ->whereNull('deleted_at')
            ->first();
        if ($user === null) {
            return Response::error404('Пользователь не найден');
        }

        try {
            DB::beginTransaction();

            if ($id == 1) {
                $data['auth_id'] = 'lemurro@lemurro';
                $data['roles'] = ['admin' => 'true'];
                $data['info_users']['email'] = 'lemurro@lemurro';
                $data['info_users']['last_name'] = 'Пользователь';
                $data['info_users']['first_name'] = 'для';
                $data['info_users']['second_name'] = 'cli-скриптов';
            }

            DB::table('users')
                ->where('id', '=', $id)
                ->whereNull('deleted_at')
                ->update([
                    'auth_id' => $data['auth_id'],
                    'updated_at' => $this->datetimenow,
                ]);

            $json_roles = json_encode($data['roles']);

            $info_users = [
                'roles' => $json_roles,
                'updated_at' => $this->datetimenow,
            ];

            if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                foreach ($data['info_users'] as $key => $value) {
                    $info_users[$key] = $value;
                }
            }

            DB::table('info_users')
                ->where('user_id', '=', $user->id)
                ->update($info_users);

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

            DB::commit();

            return (new RunAfterSave($this->dic))->run($data);
        } catch (Throwable $th) {
            DB::rollBack();

            LogException::write($this->dic['log'], $th);

            return Response::error500('Произошла ошибка при изменении пользователя, попробуйте ещё раз');
        }
    }

    /**
     * @return string|null
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    protected function getLastActionDate(int $id)
    {
        $item = DB::table('sessions')
            ->select('checked_at')
            ->where('user_id', '=', $id)
            ->orderByDesc('checked_at')
            ->first();

        if ($item === null) {
            return null;
        }

        return $item->checked_at;
    }
}
