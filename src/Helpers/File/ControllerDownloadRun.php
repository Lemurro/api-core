<?php

/**
 * Скачивание файла
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 19.06.2020
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 19.06.2020
     */
    public function start()
    {
        $file_info = (new ActionDownloadRun($this->dic))->run($this->request->get('token'));

        if (!$file_info['success']) {
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
