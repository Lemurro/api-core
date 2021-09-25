<?php
/**
 * Список справочника
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerIndex
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerIndex extends GuideController
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
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $class_name = $this->checkType($this->request->get('type'));
            $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionIndex';
            $class = new $action($this->dic);
            $this->response->setData(call_user_func([$class, 'run']));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
