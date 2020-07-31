<?php

/**
 * Инициализация приложения
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 31.07.2020
 */

namespace Lemurro\Api\Core;

use Lemurro\Api\App\Configs\SettingsMaintenance;
use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\App\Overrides\Response as AppResponse;
use Lemurro\Api\Core\Helpers\DB;
use Lemurro\Api\Core\Helpers\DIC;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Throwable;

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
     * @var Logger
     */
    protected $core_log;

    /**
     * Конструктор
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 20.12.2019
     */
    public function __construct()
    {
        date_default_timezone_set('UTC');

        $this->core_log = LoggerFactory::create('Core');

        DB::init();

        $this->initRoutes();
        $this->initDIC();
    }

    /**
     * Старт
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 31.07.2020
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

                if ($this->maintenance()) {
                    $this->response->setData(Response::error(
                        '503 Service Unavailable',
                        'warning',
                        SettingsMaintenance::MESSAGE
                    ));
                    $this->response->send();
                } else {
                    $class = $this->request->get('_controller');
                    $controller = new $class($this->request, $this->response, $this->dic);
                    call_user_func([$controller, 'start']);
                }
            }
        } catch (ResourceNotFoundException $e) {
            LogException::write($this->core_log, $e);

            $this->response->setData(Response::error404('Маршрут отсутствует'));
            $this->response->send();
        } catch (MethodNotAllowedException $e) {
            LogException::write($this->core_log, $e);

            $this->response->setData(Response::error400('Неверный метод маршрута'));
            $this->response->send();
        } catch (Throwable $t) {
            LogException::write($this->core_log, $t);

            $this->response->setData(Response::error500('Непредвиденная ошибка,<br>подробности в лог-файле'));
            $this->response->send();
        }
    }

    /**
     * Инициализация маршрутов
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 29.12.2018
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 02.08.2019
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
     * Проверка на обслуживание проекта
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 21.11.2019
     */
    protected function maintenance()
    {
        if (isset($this->dic['user']['admin']) && $this->dic['user']['admin']) {
            return false;
        }

        if (SettingsMaintenance::ACTIVE) {
            return true;
        }

        return false;
    }
}
