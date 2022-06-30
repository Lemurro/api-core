<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Изменение
 */
class ActionSave extends Action
{
    /**
     * Изменение
     *
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @return array
     */
    public function run($id, $data)
    {
        $record = (new OneRecord($this->dbal))->get((int)$id);
        if (empty($record)) {
            return Response::error404('Набор не найден');
        }

        if ((new Exist($this->dbal))->check((int)$id, (string)$data['name'])) {
            return Response::error400('Набор с таким именем уже существует');
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $record['name'] = $data['name'];
        $record['roles'] = $data['roles'];

        $this->dbal->transactional(function () use ($id, $record): void {
            $this->dbal->update('access_sets', [
                'name' => $record['name'],
                'roles' => json_encode($record['roles']),
                'updated_at' => $this->dic['datetimenow'],
            ], [
                'id' => $id
            ]);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', 'update', $id, $record);
        });

        return Response::data($record);
    }
}
