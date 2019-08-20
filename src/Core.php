<?php
/**
 * Инициализация приложения
 *
 * @version 20.08.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Exception;
use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\App\Overrides\Response as AppResponse;
use Lemurro\Api\Core\Helpers\DB;
use Lemurro\Api\Core\Helpers\DIC;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * Class Core
 *
 * @package Lemurro\Api\Core
 */
class Core
{
    /**
     * @var Container
     */
    protected $dic;

    /**
     * @var UrlMatcher
     */
    protected $url_matcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var JsonResponse
     */
    protected $response;

    /**
     * Конструктор
     *
     * @version 29.04.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        date_default_timezone_set('UTC');

        DB::init();

        $this->initRoutes();
        $this->initDIC();
    }

    /**
     * Инициализация маршрутов
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function initRoutes()
    {
        $fileLocator = new FileLocator([__DIR__, SettingsPath::FULL_ROOT]);
        $loader = new YamlFileLoader($fileLocator);
        $routes = $loader->load('coreroutes.yaml');

        $headers = [
            'X-SESSION-ID',
            'X-UTC-OFFSET',
            'X-Requested-With',
            'X-File-Name',
        ];

        $this->request = Request::createFromGlobals();
        $this->response = new JsonResponse();

        $this->response->headers->set('Access-Control-Allow-Origin', '*');
        $this->response->headers->set('Access-Control-Allow-Headers', implode(',', $headers));

        $context = new RequestContext();
        $context->fromRequest($this->request);

        $this->url_matcher = new UrlMatcher($routes, $context);
    }

    /**
     * Инициализация DIC
     *
     * @version 02.08.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function initDIC()
    {
        $this->dic = DIC::init();

        $this->dic['session_id'] = $this->request->server->get('HTTP_X_SESSION_ID');
        $this->dic['utc_offset'] = $this->request->server->get('HTTP_X_UTC_OFFSET', 0);

        $this->dic['user'] = function ($c) {
            $result_session_check = (new Session())->check($c['session_id']);
            if (isset($result_session_check['errors'])) {
                return [];
            } else {
                $user_info = (new GetUser($c))->run($result_session_check['user_id']);
                if (isset($user_info['data'])) {
                    $user_info['data']['admin'] = (isset($user_info['data']['roles']['admin']) ? true : false);

                    return $user_info['data'];
                } else {
                    return [];
                }
            }
        };
    }

    /**
     * Старт
     *
     * @version 20.08.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        try {
            $matcher = $this->url_matcher->match($this->request->getPathInfo());
            $this->request->attributes->add($matcher);

            (new AppResponse())->run($this->response);

            if ($this->request->getMethod() == 'OPTIONS') {
                $allow_methods = 'OPTIONS, ' . $this->request->headers->get('access-control-request-method');

                $this->response->headers->set('Access-Control-Allow-Methods', $allow_methods);
                $this->response->send();
            } else {
                (new AppDIC())->run($this->dic);

                $class = $this->request->get('_controller');
                $controller = new $class($this->request, $this->response, $this->dic);
                call_user_func([$controller, 'start']);
            }
        } catch (ResourceNotFoundException $e) {
            $this->response->setData(Response::error404('Маршрут отсутствует'));
            $this->response->send();
        } catch (MethodNotAllowedException $e) {
            $this->response->setData(Response::error400('Неверный метод маршрута'));
            $this->response->send();
        } catch (Exception $e) {
            LogException::write($this->dic['log'], $e);

            $this->response->setData(Response::error500('Непредвиденная ошибка,<br>подробности в лог-файле'));
            $this->response->send();
        }
    }
}
