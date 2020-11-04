<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\AccessSets;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionIndex extends Action
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run(): array
    {
        $sets = DB::table('access_sets')
            ->whereNull('deleted_at')
            ->get();

        $count = $sets->count();

        if ($count > 0) {
            foreach ($sets as &$set) {
                if (empty($set->roles)) {
                    $set->roles = [];
                } else {
                    $set->roles = json_decode($set->roles, true);
                }
            }
        }

        return Response::data([
            'count' => $count,
            'items' => $sets,
        ]);
    }
}
