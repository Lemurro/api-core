<?php

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;

/**
 * Подготовка файла к скачиванию
 */
class ControllerDownloadPrepare extends Controller
{
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = (new Checker($this->dic))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData(
                (new ActionDownloadPrepare($this->dic))->run(
                    $this->request->request->get('fileid'),
                    (string) $this->request->request->get('filename')
                )
            );
        }

        $this->response->send();
    }
}
