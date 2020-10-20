<?php

/**
 * Вход под указанным пользователем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * @package Lemurro\Api\Core\Users
 */
class ActionLoginByUser extends Action
{
    /**
     * @param integer $user_id ИД записи
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run($user_id): array
    {
        $user = (new ActionGet($this->dic))->run($user_id);
        if (isset($user['errors'])) {
            return $user;
        }

        if ((int)$user_id === 1) {
            return Response::error403('Входить под пользователем с id=1 запрещено', false);
        }

        if ((int)$user['data']['locked'] === 1) {
            return Response::error403('Пользователь заблокирован и недоступен для входа', false);
        }

        $secret = RandomKey::generate(100);
        $created_at = $this->datetimenow;

        $session = ORM::for_table('sessions')->create();
        $session->session = $secret;
        $session->user_id = $user_id;
        $session->admin_entered = 1;
        $session->created_at = $created_at;
        $session->checked_at = $created_at;

        if ($this->dic['config']['auth']['sessions_binding_to_ip']) {
            $session->ip = $_SERVER['REMOTE_ADDR'];
        }

        $session->save();

        if (is_object($session) && isset($session->id)) {
            return Response::data([
                'session' => $secret,
            ]);
        }

        return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
    }
}
