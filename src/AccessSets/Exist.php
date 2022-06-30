<?php

namespace Lemurro\Api\Core\AccessSets;

use Doctrine\DBAL\Connection;

/**
 * Проверка на наличие набора с переданным именем
 */
class Exist
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Проверка на наличие набора с переданным именем
     */
    public function check(int $id, string $name): bool
    {
        $exist_name = $this->dbal->fetchOne('SELECT name FROM access_sets WHERE name == ? AND id != ? AND deleted_at IS NULL', [$name, $id]);

        if ($exist_name !== false && $exist_name === $name) {
            return true;
        }

        return $exist_name;
    }
}
