<?php

namespace Lemurro\Api\Core\Profile;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Профиль пользователя
 */
class ControllerIndex extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 24.04.2020
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData((new ActionIndex($this->dic))->run());
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
