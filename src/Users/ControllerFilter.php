<?php
/**
 * Поиск пользователей по фильтру
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerFilter
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerFilter extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionFilter($this->dic))->run(
                $this->request->get('data')
            ));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
