<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Добавление
 */
class ActionInsert extends Action
{
    /**
     * Добавление
     *
     * @param array $data Массив данных
     *
     * @return array
     */
    public function run($data)
    {
        if ((new Exist($this->dbal))->check(0, (string)$data['name'])) {
            return Response::error400('Набор с таким именем уже существует');
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $id = $this->dbal->transactional(function () use ($data): int {
            $cnt = $this->dbal->insert('access_sets', [
                'name' => $data['name'],
                'roles' => json_encode($data['roles']),
                'created_at' => $this->dic['datetimenow'],
            ]);
            if ($cnt !== 1) {
                return Response::error500('Произошла ошибка при добавлении информации о пользователе, попробуйте ещё раз');
            }

            $id = $this->dbal->lastInsertId();

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', 'insert', $id, $data);

            return $id;
        });

        $data['id'] = $id;

        return Response::data($data);
    }
}
