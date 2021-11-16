<?php

namespace Lemurro\Api\Core\Profile\Session;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Сброс выбранной сессии
 */
class ControllerReset extends Controller
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData(
                (new ActionReset($this->dic))->run(
                    (string) $this->request->request->get('session')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
