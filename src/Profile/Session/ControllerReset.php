<?php

/**
 * Сброс выбранной сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Profile\Session;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Profile\Session
 */
class ControllerReset extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData((new ActionReset($this->dic))->run($this->request->get('session')));
        } else {
            $this->response->setData($checker_result);
        }

        return $this->response;
    }
}
