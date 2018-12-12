<?php
/**
 * Получение элемента из справочника
 *
 * @version 29.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 29.10.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionGet';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run'], $this->request->get('id')));
            } else {
                $this->response->setData([
                    'errors' => [
                        [
                            'status' => '404 Not Found',
                            'code'   => 'info',
                            'title'  => 'Неизвестный справочник',
                        ],
                    ],
                ]);
            }
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
