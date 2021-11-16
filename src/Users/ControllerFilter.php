<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Поиск пользователей по фильтру
 */
class ControllerFilter extends Controller
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
                (new ActionFilter($this->dic))->run(
                    (array) $this->request->request->get('data')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
