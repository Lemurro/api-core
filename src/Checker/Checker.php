<?php

/**
 * Какие-либо проверки перед запуском контроллера маршрута
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
 */

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class Checker
 *
 * @package Lemurro\Api\Core\Checker
 */
class Checker extends Action
{
    /**
     * Зарегистрируем пользователя по идентификатору
     *
     * @param array $checks Массив проверок
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function run($checks)
    {
        if (is_array($checks) && count($checks) > 0) {
            foreach ($checks as $check_type => $check_info) {
                switch ($check_type) {
                    case 'auth':
                        $check_result = (new Auth())->run($this->dic['session_id']);
                        break;

                    case 'role':
                        $check_result = (new Role())->run($check_info, $this->dic['user']['roles']);
                        break;

                    default:
                        $classname = 'Lemurro\\Api\\App\\Checker\\' . $check_info['class'];
                        $class = new $classname($this->dic);
                        $check_result = call_user_func([$class, 'run'], $check_info);
                        break;
                }

                if (!$check_result['success']) {
                    return $check_result;
                }
            }
        }

        return [];
    }
}
