<?php
/**
 * Разблокировать пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerUnlock
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerUnlock extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionLockUnlock($this->dic))->run(
                $this->request->get('id'),
                false
            ));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
