<?php

/**
 * Проверка аутентификации
 *
 * @version 13.04.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Session;

/**
 * Class Auth
 *
 * @package Lemurro\Api\Core\Checker
 */
class Auth
{
    /**
     * Запуск проверки
     *
     * @param string $session_id ИД сессии
     *
     * @return array
     *
     * @version 13.04.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($session_id)
    {
        return (new Session())->check($session_id);
    }
}
