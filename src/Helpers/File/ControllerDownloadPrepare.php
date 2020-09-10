<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 10.09.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ControllerDownloadPrepare extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function start(): Response
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionDownloadPrepare($this->dic))->run(
                $this->request->get('fileid'),
                $this->request->get('filename')
            ));
        }

        return $this->response;
    }
}
