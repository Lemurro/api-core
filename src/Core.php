<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core;

use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\App\Overrides\Response as AppResponse;
use Lemurro\Api\Core\Exception\ResponseException;
use Lemurro\Api\Core\Helpers\DB;
use Lemurro\Api\Core\Helpers\DIC;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Throwable;

/**
 * @package Lemurro\Api\Core
 */
class Core
{
    protected Logger $core_log;
    protected Container $dic;
    protected UrlMatcher $url_matcher;
    protected Request $request;
    protected JsonResponse $response;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(string $path_root)
    {
        date_default_timezone_set('UTC');

        $this->initRoutes($path_root);

        $this->dic = DIC::init(
            $path_root,
            (string) $this->request->server->get('HTTP_X_SESSION_ID', ''),
            (int) $this->request->server->get('HTTP_X_UTC_OFFSET', 0)
        );

        $this->core_log = $this->dic['logfactory']->create('Core');

        DB::init($this->dic['config']['database']);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
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
                        $this->dic['config']['maintenance']['message']
                    ));
                    $this->response->send();
                } else {
                    $class = $this->request->get('_controller');
                    $controller = new $class($this->request, $this->response, $this->dic);

                    /** @var SymfonyResponse $response */
                    $response = call_user_func([$controller, 'start']);
                    $response->send();
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
        } catch (ResponseException $e) {
            $this->response->setData(Response::exception($e));
            $this->response->send();
        } catch (Throwable $t) {
            LogException::write($this->core_log, $t);

            $this->response->setData(Response::error500('Непредвиденная ошибка,<br>подробности в лог-файле'));
            $this->response->send();
        }
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    protected function initRoutes(string $path_root): void
    {
        $fileLocator = new FileLocator([__DIR__, $path_root]);
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
     * Проверка на обслуживание проекта
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    protected function maintenance()
    {
        if (isset($this->dic['user']['admin']) && $this->dic['user']['admin']) {
            return false;
        }

        if ($this->dic['config']['maintenance']['active']) {
            return true;
        }

        return false;
    }
}
