<?php
/**
 * Проверка валидности сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Session;
use Pimple\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ControllerCheck
 *
 * @package Lemurro\Api\Core\Auth
 */
class ControllerCheck extends Controller
{
    /**
     * @var string
     */
    protected $session_id;

    /**
     * ControllerCheck constructor.
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

        $this->session_id = $this->dic['session_id'];
    }

    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function start()
    {
        $result_session_check = (new Session())->check($this->session_id);
        if (isset($result_session_check['errors'])) {
            $this->response->setData($result_session_check);
        } else {
            $this->response->setData(Response::data([
                'id'   => $result_session_check['session'],
            ]));
        }

        $this->response->send();
    }
}
