<?php

namespace Lemurro\Api\Core\Users;

use Doctrine\DBAL\Connection;

/**
 * Поиск пользователя
 */
class Find
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Найдем пользователя по идентификатору
     */
    public function getByAuthId(string $auth_id): ?array
    {
        $user = $this->dbal->fetchAssociative('SELECT * FROM users WHERE auth_id = ? AND deleted_at IS NULL', [$auth_id]);

        if (
            $user !== false
            && mb_strtolower($user['auth_id'], 'UTF-8') === mb_strtolower($auth_id, 'UTF-8')
        ) {
            return $user;
        }

        return null;
    }
}
