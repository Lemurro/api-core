<?php
/**
 * Добавление пользователя
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;

/**
 * Class ControllerInsert
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerInsert extends Controller
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
            'auth' => $this->di['session_id'],
            'role' => [],
        ];
        $checker_result = (new Checker($this->di))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $result = (new ActionInsert($this->di))->run($this->request->get('data'));
            $this->response->setData($result);
        }

        $this->response->send();
    }
}
