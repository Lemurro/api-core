<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\Checker
 */
class Checker extends Action
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run(array $checks): void
    {
        if (is_array($checks) && count($checks) > 0) {
            foreach ($checks as $check_type => $check_info) {
                switch ($check_type) {
                    case 'auth':
                        $check_result = (new Auth($this->dic['config']['auth']))->run($this->dic['session_id']);
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
                    Response::errorToException($check_result);
                }
            }
        }
    }
}
