<?php
/**
 * Проверка валидности сессии
 *
 * @version 24.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerCheck
 *
 * @package Lemurro\Api\Core\Auth
 */
class ControllerCheck extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 24.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $this->response->setData((new ActionCheck($this->dic))->run());
        $this->response->send();
    }
}
