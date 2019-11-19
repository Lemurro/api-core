<?php
/**
 * Какие-либо проверки перед запуском контроллера маршрута
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Abstracts\Action;
use Pimple\Container;

/**
 * Class Checker
 *
 * @package Lemurro\Api\Core\Checker
 */
class Checker extends Action
{
    /**
     * @var array
     */
    protected $user_info;

    /**
     * @var string
     */
    protected $session_id;

    /**
     * Checker constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->user_info = $this->dic['user'];
        $this->session_id = $this->dic['session_id'];
    }

    /**
     * Зарегистрируем пользователя по идентификатору
     *
     * @param array $checks Массив проверок
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function run($checks)
    {
        if (is_array($checks) && count($checks) > 0) {
            foreach ($checks as $check_type => $check_info) {
                switch ($check_type) {
                    case 'auth':
                        $check_result = (new Auth())->run($this->session_id);
                        break;

                    case 'role':
                        $check_result = (new Role())->run($check_info, $this->user_info['roles']);
                        break;

                    default:
                        $classname = 'Lemurro\\Api\\App\\Checker\\' . $check_info['class'];
                        $class = new $classname($this->dic);
                        $check_result = call_user_func([$class, 'run'], $check_info);
                        break;
                }

                if (isset($check_result['errors'])) {
                    return $check_result;
                }
            }
        }

        return [];
    }
}
