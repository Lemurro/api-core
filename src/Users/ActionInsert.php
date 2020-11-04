<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\App\RunAfter\Users\Insert as RunAfterInsert;
use Lemurro\Api\App\RunBefore\Users\Insert as RunBeforeInsert;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Throwable;

/**
 * @package Lemurro\Api\Core\Users
 */
class ActionInsert extends Action
{
    /**
     * @param array $data Массив данных
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($data): array
    {
        $data = (new RunBeforeInsert($this->dic))->run($data);

        if (!is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $check_auth_id = DB::table('users')
            ->select('id')
            ->where('auth_id', '=', $data['auth_id'])
            ->whereNull('deleted_at')
            ->get();
        if ($check_auth_id->count() > 0) {
            return Response::error400('Пользователь с такими данными для входа уже существует');
        }

        try {
            DB::beginTransaction();

            $new_user_id = DB::table('users')->insertGetId([
                'auth_id' => $data['auth_id'],
                'created_at' => $this->datetimenow,
            ]);

            $json_roles = json_encode($data['roles']);

            $new_user_info = [
                'user_id' => $new_user_id,
                'roles' => $json_roles,
                'created_at' => $this->datetimenow,
            ];

            if (isset($data['info_users']) && is_array($data['info_users']) && count($data['info_users']) > 0) {
                foreach ($data['info_users'] as $key => $value) {
                    $new_user_info[$key] = $value;
                }
            }

            DB::table('info_users')->insert($new_user_info);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('users', $data_change_log::ACTION_INSERT, $new_user_id, $data);

            $data['id'] = $new_user_id;
            $data['locked'] = false;
            $data['last_action_date'] = null;
            $data['roles'] = $json_roles;

            if (isset($data['info_users']) && !empty($data['info_users'])) {
                $data = array_merge($data, $data['info_users']);
                unset($data['info_users']);
            }

            return (new RunAfterInsert($this->dic))->run($data);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();

            LogException::write($this->dic['log'], $th);

            return Response::error500('Произошла ошибка при добавлении пользователя, попробуйте ещё раз');
        }
    }
}
