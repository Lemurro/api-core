<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ControllerRemove extends Controller
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
            'role' => [],
        ]);

        $this->response->setData((new ActionRemove($this->dic))->run(
            $this->request->get('id')
        ));

        return $this->response;
    }
}
