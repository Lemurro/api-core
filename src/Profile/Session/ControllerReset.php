<?php

/**
 * Сброс выбранной сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 11.05.2020
 */

namespace Lemurro\Api\Core\Profile\Session;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerReset
 *
 * @package Lemurro\Api\Core\Profile\Session
 */
class ControllerReset extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 11.05.2020
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData((new ActionReset($this->dic))->run($this->request->get('session')));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
