<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Guide
 */
class ControllerIndex extends Controller
{
    use CheckType;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $check_type = $this->checkType($this->request->get('type'));
            if (isset($check_type['data'])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . $check_type['data']['class'] . '\\ActionIndex';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run']));
            } else {
                $this->response->setData($check_type);
            }
        } else {
            $this->response->setData($checker_result);
        }

        return $this->response;
    }
}
