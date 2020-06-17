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
use Lemurro\Api\Core\Helpers\Response;

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
        $auth_id = $this->request->get('auth_id');

        if (empty($auth_id)) {
            $this->response->setData(Response::error400('Отсутствует параметр "auth_id"'));
        } else {
            $this->response->setData((new ActionGet($this->dic))->run($auth_id));
        }

        $this->response->send();
    }
}
