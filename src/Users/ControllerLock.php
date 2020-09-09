<?php

/**
 * Заблокировать пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * @package Lemurro\Api\Core\Users
 */
class ControllerLock extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
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
                true
            ));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
