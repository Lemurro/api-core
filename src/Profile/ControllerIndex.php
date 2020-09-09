<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Profile;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * @package Lemurro\Api\Core\Profile
 */
class ControllerIndex extends Controller
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
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && empty($checker_result)) {
            $this->response->setData((new ActionIndex($this->dic))->run());
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
