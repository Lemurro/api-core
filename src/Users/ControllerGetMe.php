<?php
/**
 * Информация о пользователе под которым пришёл запрос
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ControllerGetMe
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerGetMe extends Controller
{
    /**
     * @var array
     */
    protected $user_info;

    /**
     * ControllerGetMe constructor.
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
        parent::__construct($request, $response, $dic);

        $this->user_info = $this->dic['user'];
    }

    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function start()
    {
        if (is_array($this->user_info) && count($this->user_info) > 0) {
            $this->response->setData((new RunAfterGet($this->dic))->run($this->user_info));
        } else {
            $this->response->setData(Response::error401('Необходимо авторизоваться'));
        }

        $this->response->send();
    }
}
