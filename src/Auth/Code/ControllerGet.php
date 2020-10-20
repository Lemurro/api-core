<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.10.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Auth\Code
 */
class ControllerGet extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 20.10.2020
     */
    public function start(): Response
    {
        $this->response->setData((new ActionGet($this->dic))->run(
            $this->request->query->get('auth_id'),
            $this->request->query->get('ip', '')
        ));

        return $this->response;
    }
}
