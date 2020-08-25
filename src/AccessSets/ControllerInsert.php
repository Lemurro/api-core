<?php

/**
 * Добавление
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.08.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerInsert
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ControllerInsert extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.08.2020
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $data = json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR);

            $this->response->setData((new ActionInsert($this->dic))->run($data));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
