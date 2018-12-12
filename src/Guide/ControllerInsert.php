<?php
/**
 * Добавление элемента в справочник
 *
 * @version 29.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerInsert
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerInsert extends Controller
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
            'role' => [
                'page'   => 'guide',
                'access' => 'create-update',
            ],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionInsert';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run'], $this->request->get('data')));
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
