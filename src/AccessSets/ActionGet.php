<?php

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Получение
 */
class ActionGet extends Action
{
    /**
     * Получение
     *
     * @param integer $id ИД записи
     *
     * @return array
     */
    public function run($id)
    {
        $record = (new OneRecord($this->dbal))->get((int)$id);
        if (empty($record)) {
            return Response::error404('Набор не найден');
        }

        if (empty($record['roles'])) {
            $record['roles'] = [];
        } else {
            $record['roles'] = json_decode($record['roles'], true);
        }

        return Response::data($record);
    }
}
