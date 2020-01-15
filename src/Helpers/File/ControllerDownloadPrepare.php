<?php
/**
 * Подготовка файла к скачиванию
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 15.01.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;

/**
 * Class ControllerDownloadPrepare
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class ControllerDownloadPrepare extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 15.01.2020
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = (new Checker($this->dic))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            $this->response->setData((new ActionDownloadPrepare($this->dic))->run(
                $this->request->get('fileid'),
                $this->request->get('filename')
            ));
        }

        $this->response->send();
    }
}
