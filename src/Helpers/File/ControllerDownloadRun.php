<?php
/**
 * Скачивание файла
 *
 * @version 26.07.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class ControllerDownloadRun
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class ControllerDownloadRun extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 26.07.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $file_info = (new ActionDownloadRun($this->dic))->run($this->request->get('token'));

        if (isset($file_info['errors'])) {
            $this->response->setData($file_info);
            $this->response->send();
        }

        $filepath = $file_info['data']['filepath'];
        $filename = $file_info['data']['filename'];

        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-type', mime_content_type($filepath));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $response->send();
    }
}
