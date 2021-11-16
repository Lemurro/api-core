<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Удаление
 */
class ControllerRemove extends Controller
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
                (new ActionRemove($this->dic))->run(
                    (int) $this->request->attributes->get('id')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
