<?php
/**
 * Инициализация приложения
 *
 * @version 24.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsDatabase;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsMail;
use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\DIC as AppDIC;
use Lemurro\Api\App\Response as AppResponse;
use Lemurro\Api\Core\Checker\Checker;
use Lemurro\Api\Core\DataChangeLogs\Insert as DataChangeLogsInsert;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\SMS\SMS;
use Lemurro\Api\Core\Users\ActionGet as GetUser;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ORM;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        $this->initDatabase();
        $this->initRoutes();
        $this->initDI();
    }

    /**
     * Инициализация PDO
     *
     * @version 12.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function initDatabase()
    {
        if (SettingsDatabase::NEED_CONNECT) {
            $connection_string = 'mysql:host=' . SettingsDatabase::HOST . ';port=' . SettingsDatabase::PORT . ';dbname=' . SettingsDatabase::DBNAME;

            ORM::configure('connection_string', $connection_string);
            ORM::configure('username', SettingsDatabase::USERNAME);
            ORM::configure('password', SettingsDatabase::PASSWORD);
            ORM::configure('logging', SettingsDatabase::LOGGING);
            ORM::configure('driver_options', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]);
        }
    }

    /**
     * Инициализация маршрутов
     *
     * @version 13.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function initRoutes()
    {
        $fileLocator = new FileLocator([__DIR__, SettingsPath::FULL_ROOT]);
        $loader = new YamlFileLoader($fileLocator);
        $routes = $loader->load('coreroutes.yaml');

        $this->request = Request::createFromGlobals();
        $this->response = new JsonResponse();

        $this->response->headers->set('Access-Control-Allow-Origin', '*');
        $this->response->headers->set('Access-Control-Allow-Headers', 'X-SESSION-ID, X-UTC-OFFSET');

        $context = new RequestContext();
        $context->fromRequest($this->request);

        $this->url_matcher = new UrlMatcher($routes, $context);
    }

    /**
     * Инициализация DI
     *
     * @version 13.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function initDI()
    {
        $this->dic = new Container();

        $this->dic['session_id'] = $this->request->server->get('HTTP_X_SESSION_ID');
        $this->dic['utc_offset'] = $this->request->server->get('HTTP_X_UTC_OFFSET');

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

        $this->dic['datetimenow'] = function () {
            $now = Carbon::now('UTC');

            return $now->toDateTimeString();
        };

        $this->dic['phpmailer'] = function () {
            $phpmailer = new PHPMailer();
            $phpmailer->isHTML(true);
            $phpmailer->CharSet = 'windows-1251';
            $phpmailer->From = SettingsMail::APP_EMAIL;
            $phpmailer->FromName = iconv('utf-8', 'windows-1251', SettingsGeneral::APP_NAME);

            if (SettingsMail::SMTP) {
                $phpmailer->isSMTP();
                $phpmailer->SMTPDebug = 0;
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPSecure = SettingsMail::SMTP_SECURITY;
                $phpmailer->Host = SettingsMail::SMTP_HOST;
                $phpmailer->Port = SettingsMail::SMTP_PORT;
                $phpmailer->Username = SettingsMail::SMTP_USERNAME;
                $phpmailer->Password = SettingsMail::SMTP_PASSWORD;
            }

            return $phpmailer;
        };

        $this->dic['mailer'] = function ($c) {
            return new Mailer($c);
        };

        $this->dic['sms'] = function () {
            return new SMS();
        };

        $this->dic['datachangelog'] = function ($c) {
            return new DataChangeLogsInsert($c);
        };

        $this->dic['log'] = function () {
            $log = new Logger('MainLog');
            $log->pushHandler(new StreamHandler(SettingsPath::LOGS . 'main.log'));

            return $log;
        };

        $this->dic['checker'] = function ($c) {
            return new Checker($c);
        };
    }

    /**
     * Старт
     *
     * @version 24.12.2018
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

                $controller = $this->request->get('_controller');
                $class = new $controller($this->request, $this->response, $this->dic);
                call_user_func([$class, 'start']);
            }
        } catch (ResourceNotFoundException $e) {
            $this->response->setData(Response::error(
                '404 Not Found',
                'info',
                'Неопределённый запрос'
            ));
            $this->response->send();
        }
    }
}
