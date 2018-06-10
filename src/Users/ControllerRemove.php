<?php
/**
 * Удаление пользователя
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;

/**
 * Class ControllerRemove
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerRemove extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => $this->dic['session_id'],
            'role' => [],
        ];
        $checker_result = (new Checker($this->dic))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $result = (new ActionRemove($this->dic))->run($this->request->get('id'));
            $this->response->setData($result);
        }

        $this->response->send();
    }
}
