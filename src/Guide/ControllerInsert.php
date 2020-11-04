<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 04.11.2020
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
     * @version 04.11.2020
     */
    public function start(): Response
    {
        $this->checker->run([
            'auth' => '',
            'role' => [
                'page'   => 'guide',
                'access' => 'create-update',
            ],
        ]);

        $class_name = $this->checkType($this->request->attributes->get('type'));
        $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionInsert';
        $class = new $action($this->dic);
        $this->response->setData(call_user_func(
            [$class, 'run'],
            json_decode($this->request->request->get('json'), true, 512, JSON_THROW_ON_ERROR)
        ));

        return $this->response;
    }
}
