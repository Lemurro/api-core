<?php

/**
 * Вход под указанным пользователем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\Users
 */
class ActionLoginByUser extends Action
{
    /**
     * @param integer $user_id ИД записи
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($user_id): array
    {
        $user = (new ActionGet($this->dic))->run($user_id);
        if (isset($user['errors'])) {
            return $user;
        }

        if ((int) $user_id === 1) {
            return Response::error403('Входить под пользователем с id=1 запрещено', false);
        }

        if ((int) $user['data']['locked'] === 1) {
            return Response::error403('Пользователь заблокирован и недоступен для входа', false);
        }

        $secret = RandomKey::generate(100);
        $ip = null;
        $created_at = $this->datetimenow;

        if ($this->dic['config']['auth']['sessions_binding_to_ip']) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        DB::table('sessions')->insert([
            'session' => $secret,
            'ip' => $ip,
            'user_id' => $user_id,
            'admin_entered' => 1,
            'created_at' => $created_at,
            'checked_at' => $created_at,
        ]);

        return Response::data([
            'session' => $secret,
        ]);
    }
}
