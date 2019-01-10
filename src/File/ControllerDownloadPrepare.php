<?php
/**
 * Подготовка файла к скачиванию
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerDownloadPrepare
 *
 * @package Lemurro\Api\Core\File
 */
class ControllerDownloadPrepare extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $this->response->setData((new ActionDownloadPrepare($this->dic))->run(
            $this->request->get('fileid'),
            $this->request->get('filename')
        ));
        $this->response->send();
    }
}
