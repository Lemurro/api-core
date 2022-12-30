<?php

namespace Lemurro\Api\Core\Guide;

/**
 * Список справочника
 */
class ControllerIndex extends GuideController
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $class_name = $this->checkType((string) $this->request->attributes->get('type'));
            $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionIndex';
            $class = new $action($this->dic);
            $this->response->setData(call_user_func([$class, 'run']));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
