<?php
/**
 * Список справочника
 *
 * @version 30.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Guide;

use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsGuides;
use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Checker\Checker;
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
     * @version 30.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => $this->di['session_id'],
        ];
        $checker_result = (new Checker($this->di))->run($checker_checks);
        if (count($checker_result) > 0) {
            $this->response->setData($checker_result);
        } else {
            if (isset(SettingsGuides::CLASSES[$this->request->get('type')])) {
                $classname = 'Lemurro\\Api\\App\\Guide\\' . SettingsGuides::CLASSES[$this->request->get('type')] . '\\ActionIndex';
                $result = (new $classname($this->di))->run();
                $this->response->setData($result);
            } else {
                $this->response = new RedirectResponse(SettingsGeneral::SHORT_ROOT_PATH . 'unknown-guide-type');
            }
        }

        $this->response->send();
    }
}
