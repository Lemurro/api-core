<?php

/**
 * Информация о пользователе под которым пришёл запрос
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Users
 */
class ControllerGetMe extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $this->response->setData((new RunAfterGet($this->dic))->run(
            $this->dic['user']
        ));

        return $this->response;
    }
}
