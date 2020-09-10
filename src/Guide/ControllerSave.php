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
class ControllerSave extends Controller
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
            'role' => [
                'page'   => 'guide',
                'access' => 'create-update',
            ],
        ]);

        $check_type = $this->checkType($this->request->get('type'));
        if (isset($check_type['data'])) {
            $action = 'Lemurro\\Api\\App\\Guide\\' . $check_type['data']['class'] . '\\ActionSave';
            $class = new $action($this->dic);
            $this->response->setData(call_user_func(
                [$class, 'run'],
                $this->request->get('id'),
                json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR)
            ));
        } else {
            $this->response->setData($check_type);
        }

        return $this->response;
    }
}
