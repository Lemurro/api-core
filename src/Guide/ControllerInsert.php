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
class ControllerInsert extends Controller
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
            'role' => [
                'page'   => 'guide',
                'access' => 'create-update',
            ],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $check_type = $this->checkType($this->request->get('type'));
            if (isset($check_type['data'])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . $check_type['data']['class'] . '\\ActionInsert';
                $class = new $action($this->dic);
                $data = json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR);
                $this->response->setData(call_user_func([$class, 'run'], $data));
            } else {
                $this->response->setData($check_type);
            }
        } else {
            $this->response->setData($checker_result);
        }

        return $this->response;
    }
}
