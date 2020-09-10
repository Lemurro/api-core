<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Auth\Code;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Auth\Code
 */
class ControllerCheck extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $this->response->setData((new ActionCheck($this->dic))->run(
            $this->request->get('auth_id'),
            $this->request->get('auth_code'),
            $this->request->get('device_info'),
            $this->request->get('geoip')
        ));

        return $this->response;
    }
}
