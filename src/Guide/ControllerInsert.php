<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\Guide
 */
class ControllerInsert extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 09.09.2020
     */
    public function start()
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
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionInsert';
                $class = new $action($this->dic);

                $data = json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR);

                $this->response->setData(call_user_func([$class, 'run'], $data));
            } else {
                $this->response->setData(Response::error404('Неизвестный справочник'));
            }
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
