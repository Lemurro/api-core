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
class ActionSave extends Action
{
    /**
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($id, $data): array
    {
        $record = OneRecord::get($id);
        if ($record === null) {
            return Response::error404('Набор не найден');
        }

        $exist = Exist::check($id, $data['name']);
        if (isset($exist['errors'])) {
            return $exist;
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        DB::table('access_sets')
            ->where('id', '=', $id)
            ->update([
                'name' => $data['name'],
                'roles' => json_encode($data['roles']),
                'updated_at' => $this->datetimenow,
            ]);

        $result = (array) $record;
        $result['roles'] = $data['roles'];

        /** @var DataChangeLog $data_change_log */
        $data_change_log = $this->dic['datachangelog'];
        $data_change_log->insert('access_sets', $data_change_log::ACTION_UPDATE, $id, $result);

        return Response::data($result);
    }
}
