<?php

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Получение кода аутентификации
 */
class ControllerGet extends Controller
{
    public function start()
    {
        $this->response->setData((new ActionGet($this->dic))->run(
            (string) $this->request->query->get('auth_id'),
            (string) $this->request->query->get('ip', '')
        ));

        $this->response->send();
    }
}
