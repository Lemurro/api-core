<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.10.2020
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Guide
 */
class ControllerRemove extends Controller
{
    use CheckType;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 20.10.2020
     */
    public function start(): Response
    {
        $this->checker->run([
            'auth' => '',
            'role' => [
                'page'   => 'guide',
                'access' => 'delete',
            ],
        ]);

        $class_name = $this->checkType($this->request->query->get('type'));
        $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionRemove';
        $class = new $action($this->dic);
        $this->response->setData(call_user_func(
            [$class, 'run'],
            $this->request->query->get('id')
        ));

        return $this->response;
    }
}
