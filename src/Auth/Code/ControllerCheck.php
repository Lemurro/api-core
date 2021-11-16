<?php

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Проверка кода аутентификации
 */
class ControllerCheck extends Controller
{
    public function start()
    {
        $this->response->setData(
            (new ActionCheck($this->dic))->run(
                (string) $this->request->request->get('auth_id'),
                (string) $this->request->request->get('auth_code'),
                (array) $this->request->request->get('device_info'),
                (array) $this->request->request->get('geoip')
            )
        );
        $this->response->send();
    }
}
