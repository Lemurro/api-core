<?php

/**
 * Проверка валидности сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Session;

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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function start()
    {
        $result_session_check = (new Session())->check($this->dic['session_id']);
        if (!$result_session_check['success']) {
            $this->response->setData($result_session_check);
        } else {
            $this->response->setData(Response::data([
                'id' => $result_session_check['session'],
            ]));
        }

        $this->response->send();
    }
}
