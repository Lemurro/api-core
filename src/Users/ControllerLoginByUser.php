<?php

/**
 * Вход под указанным пользователем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.10.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Users
 */
class ControllerLoginByUser extends Controller
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

        $this->response->setData((new ActionLoginByUser($this->dic))->run(
            $this->request->request->get('user_id')
        ));

        return $this->response;
    }
}
