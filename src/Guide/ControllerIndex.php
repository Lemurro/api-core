<?php
/**
 * Список справочника
 *
 * @version 17.08.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @version 17.08.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $action = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionIndex';
                $class = new $action($this->dic);
                $this->response->setData(call_user_func([$class, 'run']));
            } else {
                $this->response = new RedirectResponse(SettingsGeneral::SHORT_ROOT_PATH . 'unknown-guide-type');
            }
        }

        $this->response->send();
    }
}
