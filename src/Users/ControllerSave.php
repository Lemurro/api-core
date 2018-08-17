<?php
/**
 * Изменение пользователя
 *
 * @version 17.08.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerSave
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerSave extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 17.08.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionSave($this->dic))->run($this->request->get('id'), $this->request->get('data')));
        }

        $this->response->send();
    }
}
