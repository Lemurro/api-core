<?php
/**
 * Вход под указанным пользователем
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsAuth;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionLoginByUser
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionLoginByUser extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $user_id ИД записи
     *
     * @return array
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($user_id)
    {
        $user = (new ActionGet($this->dic))->run($user_id);
        if (isset($user['errors'])) {
            return $user;
        }

        $secret = RandomKey::generate(100);
        $created_at = $this->dic['datetimenow'];

        $session = ORM::for_table('sessions')->create();
        $session->session = $secret;
        $session->user_id = $user_id;
        $session->created_at = $created_at;
        $session->checked_at = $created_at;

        if (SettingsAuth::SESSIONS_BINDING_TO_IP) {
            $session->ip = $_SERVER['REMOTE_ADDR'];
        }

        $session->save();

        if (is_object($session) AND isset($session->id)) {
            return Response::data([
                'session' => $secret,
            ]);
        } else {
            return Response::error500('Произошла ошибка при аутентификации, попробуйте ещё раз');
        }
    }
}
