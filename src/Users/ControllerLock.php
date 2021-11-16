<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Заблокировать пользователя
 */
class ControllerLock extends Controller
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData(
                (new ActionLockUnlock($this->dic))->run(
                    (int) $this->request->attributes->get('id'),
                    true
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
