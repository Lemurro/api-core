<?php
/**
 * Вход под указанным пользователем
 *
 * @version 29.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerLoginByUser
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerLoginByUser extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 29.10.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionLoginByUser($this->dic))->run($this->request->get('user_id')));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
