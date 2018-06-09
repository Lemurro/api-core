<?php
/**
 * Какие-либо проверки перед запуском контроллера маршрута
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
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
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($checks)
    {
        if (is_array($checks) && count($checks) > 0) {
            foreach ($checks as $check_type => $check_info) {
                switch ($check_type) {
                    case 'auth':
                        $check_result = (new Auth())->run($this->di['session_id']);
                        break;

                    case 'role':
                        $check_result = (new Role())->run($check_info, $this->di['user']['roles']);
                        break;

                    default:
                        $classname = 'Lemurro\\Api\\App\\Checker\\' . $check_info['class'];
                        $check_result = (new $classname($this->di))->run($check_info);
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
