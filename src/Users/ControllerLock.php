<?php
/**
 * Заблокировать пользователя
 *
 * @version 03.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerLock
 *
 * @package Lemurro\Api\Core\Users
 */
class ControllerLock extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 03.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $checker_checks = [
            'auth' => '',
            'role' => [],
        ];
        $checker_result = $this->dic['checker']->run($checker_checks);
        if (is_array($checker_result) && count($checker_result) == 0) {
            $this->response->setData((new ActionLockUnlock($this->dic))->run(
                $this->request->get('id'),
                true
            ));
        } else {
            $this->response->setData($checker_result);
        }

        $this->response->send();
    }
}
