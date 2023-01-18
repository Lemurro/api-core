<?php

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Какие-либо проверки перед запуском контроллера маршрута
 */
class Checker extends Action
{
    /**
     * Какие-либо проверки перед запуском контроллера маршрута
     *
     * @param array $checks Массив проверок
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function run($checks): array
    {
        if (is_array($checks) && count($checks) > 0) {
            foreach ($checks as $check_type => $check_info) {
                switch ($check_type) {
                    case 'auth':
                        $check_result = (new Auth($this->dbal))->run((string)$this->dic['session_id']);
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

                if (isset($check_result['errors'])) {
                    return $check_result;
                }
            }
        }

        return [];
    }
}
