<?php
/**
 * Список пользователей
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 27.09.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerIndex
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerIndex extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 27.09.2019
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionFilter($this->dic))->run([]));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
