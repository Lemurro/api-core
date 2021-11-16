<?php

namespace Lemurro\Api\Core\Guide;

/**
 * Добавление элемента в справочник
 */
class ControllerInsert extends GuideController
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [
                'page' => 'guide',
                'access' => 'create-update',
            ],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $class_name = $this->checkType((string) $this->request->attributes->get('type'));
            $action = 'Lemurro\\Api\\App\\Guide\\'.$class_name.'\\ActionInsert';
            $class = new $action($this->dic);
            $this->response->setData(
                call_user_func(
                    [$class, 'run'],
                    (array) $this->request->request->get('data')
                )
            );
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
