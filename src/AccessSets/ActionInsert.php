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
class ActionInsert extends Action
{
    /**
     * @param array $data Массив данных
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run($data): array
    {
        $exist = Exist::check(0, $data['name']);
        if (isset($exist['errors'])) {
            return $exist;
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $id = DB::table('access_sets')->insertGetId([
            'name' => $data['name'],
            'roles' => json_encode($data['roles']),
            'created_at' => $this->datetimenow,
        ]);

        $data['id'] = $id;
        $data['created_at'] = $this->datetimenow;

        /** @var DataChangeLog $data_change_log */
        $data_change_log = $this->dic['datachangelog'];
        $data_change_log->insert('access_sets', $data_change_log::ACTION_INSERT, $id, $data);

        return Response::data($data);
    }
}
