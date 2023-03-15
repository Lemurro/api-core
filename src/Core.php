<?php

namespace Lemurro\Api\Core;

use Doctrine\DBAL\Connection;
use Exception;
use Lemurro\Api\App\Configs\SettingsMaintenance;
use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\Overrides\DIC as AppDIC;
use Lemurro\Api\App\Overrides\Response as AppResponse;
use Lemurro\Api\Core\Helpers\DB;
use Lemurro\Api\Core\Helpers\DIC;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\ResponseException;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Monolog\Logger;
use Pimple\Container;
use RuntimeException;
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
 * Инициализация приложения
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

    public function __construct()
    {
        date_default_timezone_set('UTC');

        $this->core_log = LoggerFactory::create('Core');

        try {
            $dbal = DB::init();
            if ($dbal === null) {
                throw new RuntimeException('Не удалось подключиться к БД', 500);
            }

            $this->initRoutes();
            $this->initDIC($dbal);
        } catch (Exception $e) {
            LogException::write($this->core_log, $e);

            throw $e;
        }
    }

    /**
     * Старт
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
                    $class_name = 'Lemurro\\Api\\App\\Middlewares\\MiddlewareForAll';
                    /** @psalm-suppress TypeDoesNotContainType */
                    if (class_exists($class_name)) {
                        $middleware = new $class_name($this->request, $this->response, $this->dic);
                        $this->response = call_user_func([$middleware, 'execute']);
                    }

                    $route_middleware = $this->request->attributes->get('middleware');
                    if (!empty($route_middleware)) {
                        if (class_exists($route_middleware)) {
                            $middleware = new $route_middleware($this->request, $this->response, $this->dic);
                            $this->response = call_user_func([$middleware, 'execute']);
                        }
                    }

                    $class = (string) $this->request->attributes->get('_controller');
                    $controller = new $class($this->request, $this->response, $this->dic);
                    call_user_func([$controller, 'start']);
                }
            }
        } catch (ResponseException $e) {
            $this->response->setData(Response::exception($e));
            $this->response->send();
        } catch (ResourceNotFoundException $e) {
            LogException::write($this->core_log, $e);

            $this->response->setData(Response::error404('Маршрут отсутствует'));
            $this->response->send();
        } catch (MethodNotAllowedException $e) {
            LogException::write($this->core_log, $e);

            $this->response->setData(Response::error400('Неверный метод маршрута'));
            $this->response->send();
        } catch (Throwable $e) {
            /** @psalm-suppress ArgumentTypeCoercion */
            LogException::write($this->core_log, $e);

            $this->response->setData(Response::error500('Непредвиденная ошибка,<br>подробности в лог-файле'));
            $this->response->send();
        }
    }

    /**
     * Инициализация маршрутов
     */
    protected function initRoutes()
    {
        $fileLocator = new FileLocator([__DIR__, SettingsPath::FULL_ROOT]);
        $loader = new YamlFileLoader($fileLocator);
        $routes = $loader->load('coreroutes.yaml');

        $this->request = Request::createFromGlobals();
        $this->response = new JsonResponse();

        $this->response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | \JSON_UNESCAPED_UNICODE);

        $this->response->headers->set('Access-Control-Allow-Origin', '*');

        $context = new RequestContext();
        $context->fromRequest($this->request);

        $this->url_matcher = new UrlMatcher($routes, $context);
    }

    /**
     * Инициализация DIC
     */
    protected function initDIC(Connection $dbal): void
    {
        $this->dic = DIC::init($dbal);

        $this->dic['session_id'] = (string)$this->request->server->get('HTTP_X_SESSION_ID');
        $this->dic['utc_offset'] = (int)$this->request->server->get('HTTP_X_UTC_OFFSET', 0);

        $this->dic['user'] = function ($c) use ($dbal) {
            $result_session_check = (new Session($dbal))->check($c['session_id']);
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
     */
    protected function maintenance(): bool
    {
        if (isset($this->dic['user']['admin']) && $this->dic['user']['admin']) {
            return false;
        }

        return SettingsMaintenance::ACTIVE;
    }
}
