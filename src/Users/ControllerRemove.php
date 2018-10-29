<?php
/**
 * Удаление пользователя
 *
 * @version 29.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

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
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionRemove($this->dic))->run($this->request->get('id')));
        }

        $this->response->send();
    }
}
