<?php
/**
 * Получение кода аутентификации
 *
 * @version 13.07.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * @version 13.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $this->response->setData((new ActionGet($this->dic))->run($this->request->get('auth_id')));
        $this->response->send();
    }
}
