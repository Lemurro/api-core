<?php

namespace Lemurro\Api\Core\AccessSets;

use Doctrine\DBAL\Connection;

/**
 * Получим одну запись по ИД
 */
class OneRecord
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Получим одну запись по ИД
     */
    public function get(int $id): ?array
    {
        $record = $this->dbal->fetchAssociative('SELECT * FROM access_sets WHERE id = ? AND deleted_at IS NULL', [$id]);
        if ($record === false) {
            return null;
        }

        return $record;
    }
}
