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
class ControllerGet extends Controller
{
    use CheckType;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $this->checker->run([
            'auth' => '',
        ]);

        $class_name = $this->checkType($this->request->get('type'));
        $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionGet';
        $class = new $action($this->dic);
        $this->response->setData(call_user_func(
            [$class, 'run'],
            $this->request->get('id')
        ));

        return $this->response;
    }
}
