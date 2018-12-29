<?php
/**
 * Информация о пользователе
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\User;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\User
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $user_info = $this->dic['user'];
        if (is_array($user_info) && count($user_info) > 0) {
            $this->response->setData(Response::data($user_info));
        } else {
            $this->response->setData(Response::error401('Необходимо авторизоваться'));
        }

        $this->response->send();
    }
}
