<?php
/**
 * Проверка кода аутентификации
 *
 * @version 10.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * @version 10.10.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $this->response->setData((new ActionCheck($this->dic))->run(
            $this->request->get('auth_id'),
            $this->request->get('auth_code'),
            $this->request->get('device_info')
        ));
        $this->response->send();
    }
}
