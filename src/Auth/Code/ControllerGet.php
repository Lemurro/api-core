<?php

/**
 * Получение кода аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 17.06.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\Auth\Code
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.06.2020
     */
    public function start()
    {
        $this->response->setData((new ActionGet($this->dic))->run(
            (string) $this->request->query->get('auth_id'),
            (string) $this->request->query->get('ip', '')
        ));

        $this->response->send();
    }
}
