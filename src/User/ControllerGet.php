<?php
/**
 * Информация о пользователе
 *
 * @version 16.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\User;

use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
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
     * @version 16.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $user_info = $this->dic['user'];
        if (is_array($user_info) && count($user_info) > 0) {
            $this->response->setData((new RunAfterGet($this->dic))->run($user_info));
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
