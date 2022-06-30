<?php

namespace Lemurro\Api\Core\Checker;

use Doctrine\DBAL\Connection;
use Lemurro\Api\Core\Session;

/**
 * Проверка аутентификации
 */
class Auth
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Проверка аутентификации
     */
    public function run(string $session_id): array
    {
        return (new Session($this->dbal))->check($session_id);
    }
}
