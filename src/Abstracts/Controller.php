<?php
/**
 * Шаблон контроллера
 *
 * @version 06.06.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Abstracts;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Controller
 *
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var JsonResponse
     */
    protected $response;

    /**
     * @var Container
     */
    protected $di;

    /**
     * Конструктор
     *
     * @param Request      $request  Объект запроса
     * @param JsonResponse $response Объект ответа
     * @param Container    $di       Объект контейнера зависимостей
     *
     * @version 06.06.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct($request, $response, $di)
    {
        $this->request = $request;
        $this->response = $response;
        $this->di = $di;
    }

    /**
     * Стартовый метод
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    abstract public function start();
}
