<?php
/**
 * Список справочника
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerIndex
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerIndex extends Controller
{
    /**
     * Стартовый метод
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionIndex';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run']));
            } else {
                $this->response->setData(Response::error404('Неизвестный справочник'));
            }
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
