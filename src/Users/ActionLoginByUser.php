<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Вход под указанным пользователем
 */
class ActionLoginByUser extends Action
{
    /**
     * Вход под указанным пользователем
     *
     * @param integer $user_id ИД записи
     */
    public function run($user_id): array
    {
        $user = (new ActionGet($this->dic))->run($user_id);
        if (isset($user['errors'])) {
            return $user;
        }

        $user = $user['data'];

        if ((int) $user_id === 1) {
            return Response::error403('Входить под пользователем с id=1 запрещено', false);
        }

        if (isset($user['roles']['admin']) && (bool) $user['roles']['admin'] === true) {
            return Response::error403('Пользователь является администратором и недоступен для входа', false);
        }

        if ((int) $user['locked'] === 1) {
            return Response::error403('Пользователь заблокирован и недоступен для входа', false);
        }

        $secret = RandomKey::generate(100);
        $created_at = $this->dic['datetimenow'];

        $ip = null;
        if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        $session = ORM::for_table('sessions')->create();
        $session->session = $secret;
        $session->ip = $ip;
        $session->user_id = $user_id;
        $session->admin_entered = 1;
        $session->created_at = $created_at;
        $session->checked_at = $created_at;

        $session->save();

        if (is_object($session) && isset($session->id)) {
            return Response::data([
                'session' => $secret,
            ]);
        }

        return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
    }
}
