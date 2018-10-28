<?php
/**
 * Проверка валидности сессии
 *
 * @version 28.10.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Controller;
use Lemurro\Api\Core\Session;

/**
 * Class ControllerCheck
 *
 * @package Lemurro\Api\Core\Auth
 */
class ControllerCheck extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 28.10.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $result_session_check = (new Session())->check($this->dic['session_id']);
        if (isset($result_session_check['errors'])) {
            $this->response->setData($result_session_check);
        } else {
            $this->response->setData([
                'data' => [
                    'id'   => $result_session_check['session'],
                ],
            ]);
        }

        $this->response->send();
    }
}
