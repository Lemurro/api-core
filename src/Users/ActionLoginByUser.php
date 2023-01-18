<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;

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

        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsGeneral::SERVER_TYPE === SettingsGeneral::SERVER_TYPE_PROD) {
            if (isset($user['roles']['admin']) && (bool) $user['roles']['admin'] === true) {
                return Response::error403('Пользователь является администратором и недоступен для входа', false);
            }

            if ((int) $user['locked'] === 1) {
                return Response::error403('Пользователь заблокирован и недоступен для входа', false);
            }
        }

        $secret = RandomKey::generate(100);
        $created_at = $this->dic['datetimenow'];

        $ip = null;
        /** @psalm-suppress TypeDoesNotContainType */
        if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        $cnt = $this->dbal->insert('sessions', [
            'session' => $secret,
            'ip' => $ip,
            'user_id' => $user_id,
            'admin_entered' => 1,
            'created_at' => $created_at,
            'checked_at' => $created_at,
        ]);
        if ($cnt !== 1) {
            return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
        }

        return Response::data([
            'session' => $secret,
        ]);
    }
}
