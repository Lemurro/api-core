<?php

/**
 * Вход под указанным пользователем
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
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
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionLoginByUser($this->dic))->run($this->request->get('user_id')));
        } else {
            $this->response->setData($checker_result);
        }

        return $this->response;
    }
}
