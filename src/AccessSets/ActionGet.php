<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionGet extends Action
{
    /**
     * @param integer $id ИД записи
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($id): array
    {
        $record = OneRecord::get($id);

        if ($record === null) {
            return Response::error404('Набор не найден');
        }

        $record = (array) $record;

        if (empty($record['roles'])) {
            $record['roles'] = [];
        } else {
            $record['roles'] = json_decode($record['roles'], true);
        }

        return Response::data($record);
    }
}
