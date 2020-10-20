<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class ControllerDownloadRun extends Controller
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 20.10.2020
     */
    public function start(): Response
    {
        $file_info = (new ActionDownloadRun($this->dic))->run($this->request->query->get('token'));

        if (isset($file_info['errors'])) {
            $this->response->setData($file_info);

            return $this->response;
        }

        $filepath = $file_info['data']['filepath'];
        $filename = $file_info['data']['filename'];

        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-type', mime_content_type($filepath));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}
