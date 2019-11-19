<?php
/**
 * Получение
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ControllerGet extends Controller
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
            'role' => [],
        ];
        $checker_result = $this->checker->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionGet($this->dic))->run(
                $this->request->get('id')
            ));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
