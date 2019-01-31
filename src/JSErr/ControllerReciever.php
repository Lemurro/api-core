<?php
/**
 * Получение javascript-ошибок от client-framework7 и client-metronic
 *
 * @example http://path-to-api/jserrors?msg=message&file=file.js&line=32&col=3&err={"json-string"}
 *
 * @version 31.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\JSErr;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\LoggerFactory;

/**
 * Class ControllerReciever
 *
 * @package Lemurro\Api\Core\JSErr
 */
class ControllerReciever extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 31.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $one_pixel_image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD//gATQ3JlYXRlZCB3aXRoIEdJTVD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wgARCAABAAEDAREAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAVSf/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIAAwAAABCf/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPxB//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPxB//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxB//9k=';

        $array = [
            $this->request->get('msg'),
            'File: ' . $this->request->get('file'),
            'Line: ' . $this->request->get('line'),
            'Col: ' . $this->request->get('col'),
            'Err: ' . $this->request->get('err'),
        ];

        $log = LoggerFactory::create('JSErr');

        $log->error(implode(' | ', $array));

        echo $one_pixel_image;
    }
}
