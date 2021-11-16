<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Вход под указанным пользователем
 */
class ControllerLoginByUser extends Controller
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
                (new ActionLoginByUser($this->dic))->run(
                    (int) $this->request->request->get('user_id')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
