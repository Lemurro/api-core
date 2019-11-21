<?php
/**
 * Удаление пользователя
 *
 * @version 03.04.2019
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
     * @version 03.04.2019
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
            $this->response->setData((new ActionRemove($this->dic))->run($this->request->get('id')));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
