<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 01.12.2020
 */

namespace Lemurro\Api\Core;

use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\App\Overrides\Response as AppResponse;
use Lemurro\Api\Core\Exception\ResponseException;
use Lemurro\Api\Core\Helpers\Database;
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
    protected Request $request;
    protected JsonResponse $response;
    protected Container $dic;
    protected Logger $core_log;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 01.12.2020
     */
    public function __construct(string $path_root, string $sql_driver)
    {
        date_default_timezone_set('UTC');

        $this->request = Request::createFromGlobals();
        $this->response = new JsonResponse();

        $this->dic = DIC::init(
            $path_root,
            (string) $this->request->server->get('HTTP_X_SESSION_ID', ''),
            (int) $this->request->server->get('HTTP_X_UTC_OFFSET', 0)
        );

        $this->core_log = $this->dic['logfactory']->create('Core');

        (new Database())->addConnection($this->dic['config']['database'][$sql_driver])->connect();
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 21.10.2020
     */
    public function start()
    {
        try {
            $this->initHeaders();
            $this->initRoutes();
            $this->initApplication();
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
     * @version 26.10.2020
     */
    protected function initHeaders(): void
    {
        $origin = $this->getOrigin($this->request->headers->get('Origin', ''));
        $credentials = $this->dic['config']['cors']['access_control_allow_credentials'];
        $headers = implode(',', $this->dic['config']['headers']);

        $this->response->headers->set('Access-Control-Allow-Origin', $origin);
        $this->response->headers->set('Access-Control-Allow-Credentials', $credentials);
        $this->response->headers->set('Access-Control-Allow-Headers', $headers);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 21.10.2020
     */
    protected function initRoutes(): void
    {
        $fileLocator = new FileLocator([__DIR__, $this->dic['path_root']]);
        $loader = new YamlFileLoader($fileLocator);
        $routes = $loader->load('coreroutes.yaml');

        $context = new RequestContext();
        $context->fromRequest($this->request);

        $url_matcher = new UrlMatcher($routes, $context);
        $matcher = $url_matcher->match($this->request->getPathInfo());
        $this->request->attributes->add($matcher);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 21.10.2020
     */
    protected function initApplication(): void
    {
        (new AppResponse())->run($this->response);

        if ($this->request->getMethod() == 'OPTIONS') {
            $allow_methods = 'OPTIONS, ' . $this->request->headers->get('Access-Control-Request-Method');

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

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 21.10.2020
     */
    protected function getOrigin(string $request_origin): string
    {
        $allow_origins = $this->dic['config']['cors']['access_control_allow_origin'];

        if (in_array('*', $allow_origins)) {
            return '*';
        }

        if (in_array($request_origin, $allow_origins)) {
            return $request_origin;
        }

        return $allow_origins[0];
    }
}
