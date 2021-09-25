<?php
/**
 * Удаление элемента из справочника
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerRemove
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerRemove extends GuideController
{
    /**
     * Стартовый метод
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [
                'page'   => 'guide',
                'access' => 'delete',
            ],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $class_name = $this->checkType($this->request->get('type'));
            $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionRemove';
            $class = new $action($this->dic);
            $this->response->setData(call_user_func([$class, 'run'], $this->request->get('id')));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
