<?php

namespace Lemurro\Api\Core\Guide;

/**
 * Удаление элемента из справочника
 */
class ControllerRemove extends GuideController
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [
                'page' => 'guide',
                'access' => 'delete',
            ],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $class_name = $this->checkType((string) $this->request->attributes->get('type'));
            $action = 'Lemurro\\Api\\App\\Guide\\' . $class_name . '\\ActionRemove';
            $class = new $action($this->dic);
            $this->response->setData(
                call_user_func(
                    [$class, 'run'],
                    (int) $this->request->attributes->get('id')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
