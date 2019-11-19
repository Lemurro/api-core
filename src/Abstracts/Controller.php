<?php
/**
 * Шаблон контроллера
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\Core\Checker\Checker;
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
    protected $dic;

    /**
     * @var Checker
     */
    protected $checker;

    /**
     * Конструктор
     *
     * @param Request      $request  Объект запроса
     * @param JsonResponse $response Объект ответа
     * @param Container    $dic      Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($request, $response, $dic)
    {
        $this->request = $request;
        $this->response = $response;
        $this->dic = $dic;
        $this->checker = $dic['checker'];
    }

    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 26.05.2018
     */
    abstract public function start();
}
