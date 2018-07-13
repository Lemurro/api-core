<?php
/**
 * Получение пользователя
 *
 * @version 13.07.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 13.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = (new Checker($this->dic))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionGet($this->dic))->run($this->request->get('id')));
        }

        $this->response->send();
    }
}
