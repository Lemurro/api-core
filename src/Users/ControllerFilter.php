<?php

/**
 * Поиск пользователей по фильтру
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * @package Lemurro\Api\Core\Users
 */
class ControllerFilter extends Controller
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
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $data = json_decode($this->request->get('json'), true, 512, JSON_THROW_ON_ERROR);

            $this->response->setData((new ActionFilter($this->dic))->run($data));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
