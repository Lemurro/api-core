<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 09.09.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ControllerUpload extends Controller
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
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionUpload($this->dic))->run($this->request->files));
        }

        $this->response->send();
    }
}
