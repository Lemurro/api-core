<?php
/**
 * Скачивание файла
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class ControllerDownloadRun
 *
 * @package Lemurro\Api\Core\File
 */
class ControllerDownloadRun extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $file_info = (new FileToken($this->dic))->getFileInfo($this->request->get('token'));

        if (isset($file_info['errors'])) {
            $this->response->setData($file_info);
            $this->response->send();
        }

        $filepath = SettingsFile::FILE_FOLDER . $file_info['data']['path'];

        if (is_readable($filepath) && is_file($filepath)) {
            $response = new BinaryFileResponse($filepath);
            $response->headers->set('Content-type', mime_content_type($filepath));
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file_info['data']['name']);
            $response->send();
        } else {
            $this->response->setData(Response::error404('Файл не найден'));
            $this->response->send();
        }
    }
}
