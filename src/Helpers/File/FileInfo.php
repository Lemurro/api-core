<?php

namespace Lemurro\Api\Core\Helpers\File;

use Doctrine\DBAL\Connection;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Получим информацию по одному или нескольким файлам
 */
class FileInfo
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Получим информацию по одному файлу
     *
     * @param integer $id ИД файла
     */
    public function getById($id): ?array
    {
        $info = $this->dbal->fetchAssociative('SELECT * FROM files WHERE id = ? AND deleted_at IS NULL', [$id]);
        if ($info === false) {
            return null;
        }

        return $info;
    }

    /**
     * Получим информацию по одному файлу
     *
     * @param integer $id ИД файла
     */
    public function getOne($id): array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                name,
                ext,
                created_at
            FROM files
            WHERE id = ?
                AND deleted_at IS NULL
            SQL;

        $info = $this->dbal->fetchAssociative($sql, [$id]);
        if ($info === false) {
            return Response::error404('Файл не найден');
        }

        return Response::data($info);
    }

    /**
     * Получим информацию по нескольким файлам
     *
     * @param array $ids ИД файлов
     */
    public function getMany($ids): array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                name,
                ext,
                created_at
            FROM files
            WHERE id IN (?)
                AND deleted_at IS NULL
            SQL;

        return Response::data(
            $this->dbal->fetchAllAssociative($sql, [$ids], [Connection::PARAM_INT_ARRAY])
        );
    }
}
