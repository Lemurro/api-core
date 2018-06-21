<?php
/**
 * Изменение элемента в справочнике
 *
 * @version 21.06.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ControllerSave
 *
 * @package Lemurro\Api\Core\Guide
 */
class ControllerSave extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 21.06.2018
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
        $checker_result = (new Checker($this->dic))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $classname = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionSave';
                $result = (new $classname($this->dic))->run($this->request->get('id'), $this->request->get('data'));
                $this->response->setData($result);
            } else {
                $this->response = new RedirectResponse(SettingsGeneral::SHORT_ROOT_PATH . 'unknown-guide-type');
            }
        }

        $this->response->send();
    }
}