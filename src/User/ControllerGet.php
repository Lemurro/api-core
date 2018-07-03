<?php
/**
 * Информация о пользователе
 *
 * @version 03.07.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\User;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\User
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 03.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $user_info = $this->dic['user'];
        if (count($user_info) > 0) {
            $this->response->setData([
                'data' => $user_info,
            ]);
        } else {
            $this->response->setData([
                'errors' => [
                    [
                        'status' => '401 Unauthorized',
                        'code'   => 'info',
                        'title'  => 'Необходимо авторизоваться',
                    ],
                ],
            ]);
        }

        $this->response->send();
    }
}
