<?php
/**
 * Информация о пользователе под которым пришёл запрос
 *
 * @version 26.07.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerGetMe
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerGetMe extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 26.07.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $user_info = $this->dic['user'];

        if (is_array($user_info) && count($user_info) > 0) {
            $this->response->setData((new RunAfterGet($this->dic))->run($user_info));
        } else {
            $this->response->setData(Response::error401('Необходимо авторизоваться'));
        }

        $this->response->send();
    }
}
