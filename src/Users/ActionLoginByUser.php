<?php
/**
 * Вход под указанным пользователем
 *
 * @version 10.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\RandomKey;

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
     * @version 10.10.2018
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

        $session = \ORM::for_table('sessions')->create();
        $session->session = $secret;
        $session->user_id = $user_id;
        $session->created_at = $created_at;
        $session->checked_at = $created_at;

        if (SettingsGeneral::SESSIONS_BINDING_TO_IP) {
            $session->ip = $_SERVER['REMOTE_ADDR'];
        }

        $session->save();

        if (is_object($session) AND isset($session->id)) {
            return [
                'data' => [
                    'session' => $secret,
                ],
            ];
        } else {
            return [
                'errors' => [
                    [
                        'status' => '500 Internal Server Error',
                        'code'   => 'danger',
                        'title'  => 'Произошла ошибка при аутентификации, попробуйте ещё раз',
                    ],
                ],
            ];
        }
    }
}