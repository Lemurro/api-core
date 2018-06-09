<?php
/**
 * Проверка валидности сессии
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Session;
use Lemurro\Api\Core\Users\ActionGet as GetUser;

/**
 * Class ControllerCheck
 *
 * @package Lemurro\Api\Core\Auth
 */
class ControllerCheck extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $result_session_check = (new Session())->check($this->di['session_id']);
        if (isset($result_session_check['errors'])) {
            $this->response->setData($result_session_check);
        } else {
            $user_info = (new GetUser($this->di))->run($result_session_check['user_id']);
            if (isset($user_info['data'])) {
                $user = $user_info['data'];
            } else {
                $user = [];
            }

            $this->response->setData([
                'data' => [
                    'id'   => $result_session_check['session'],
                    'user' => $user,
                ],
            ]);
        }

        $this->response->send();
    }
}
