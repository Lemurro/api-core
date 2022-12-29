<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Удаление
 */
class ActionRemove extends Action
{
    /**
     * Удаление
     *
     * @param integer $id ИД записи
     *
     * @return array
     */
    public function run($id)
    {
        if (empty((new OneRecord($this->dbal))->get((int)$id))) {
            return Response::error404('Набор не найден');
        }

        $this->dbal->transactional(function () use ($id): void {
            $this->dbal->update('access_sets', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'id' => $id
            ]);

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', 'delete', $id);
        });

        return Response::data([
            'id' => $id,
        ]);
    }
}
