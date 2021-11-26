<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionRemove extends Action
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
        $affected = DB::table('access_sets')
            ->where('id', '=', $id)
            ->update([
                'deleted_at' => $this->datetimenow,
            ]);

        if ($affected === 1) {
            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', $data_change_log::ACTION_DELETE, $id);
        }

        return Response::data([
            'id' => $id,
        ]);
    }
}
