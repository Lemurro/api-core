<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ControllerInsert extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 20.10.2020
     */
    public function start(): Response
    {
        $this->checker->run([
            'auth' => '',
            'role' => [],
        ]);

        $this->response->setData((new ActionInsert($this->dic))->run(
            json_decode($this->request->request->get('json'), true, 512, JSON_THROW_ON_ERROR)
        ));

        return $this->response;
    }
}
