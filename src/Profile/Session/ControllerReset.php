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
        $this->checker->run([
            'auth' => '',
        ]);

        $this->response->setData((new ActionReset($this->dic))->run(
            $this->request->get('session')
        ));

        return $this->response;
    }
}
