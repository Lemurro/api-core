<?php
/**
 * Получение кода аутентификации
 *
 * @version 26.05.2018
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
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $result = (new ActionGet($this->di))->run($this->request->get('auth_id'));
        $this->response->setData($result);
        $this->response->send();
    }
}
