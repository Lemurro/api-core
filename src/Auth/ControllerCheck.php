<?php

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Проверка валидности сессии
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
