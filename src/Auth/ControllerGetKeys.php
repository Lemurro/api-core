<?php
/**
 * Просмотр ключей доступа
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 17.04.2020
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGetKeys
 *
 * @package Lemurro\Api\Core\Auth
 */
class ControllerGetKeys extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData((new ActionGetKeys($this->dic))->run());
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
