<?php
/**
 * Проверка кода аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 24.04.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerCheck
 *
 * @package Lemurro\Api\Core\Auth\Code
 */
class ControllerCheck extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 24.04.2020
     */
    public function start()
    {
        $this->response->setData((new ActionCheck($this->dic))->run(
            $this->request->get('auth_id'),
            $this->request->get('auth_code'),
            $this->request->get('device_info'),
            $this->request->get('geoip')
        ));
        $this->response->send();
    }
}
