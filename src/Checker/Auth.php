<?php

/**
 * Проверка аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Checker;

use Lemurro\Api\Core\Session;

/**
 * @package Lemurro\Api\Core\Checker
 */
class Auth
{
    private Session $session;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_auth)
    {
        $this->session = new Session($config_auth);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run(string $session_id): array
    {
        return $this->session->check($session_id);
    }
}
