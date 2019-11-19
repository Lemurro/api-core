<?php
/**
 * Удаление элемента из справочника
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ControllerRemove
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerRemove extends Controller
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
            'role' => [
                'page'   => 'guide',
                'access' => 'delete',
            ],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionRemove';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run'], $this->request->get('id')));
            } else {
                $this->response->setData(Response::error404('Неизвестный справочник'));
            }
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
