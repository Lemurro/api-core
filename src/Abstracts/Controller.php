<?php

/**
 * Шаблон контроллера
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\Core\Checker\Checker;
use Pimple\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class Controller
{
    protected Request $request;
    protected JsonResponse $response;
    protected Container $dic;
    protected Checker $checker;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
     */
    public function __construct(Request $request, JsonResponse $response, Container $dic)
    {
        $this->request = $request;
        $this->response = $response;
        $this->dic = $dic;

        $this->checker = new Checker($dic);
    }

    /**
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    abstract public function start();
}
