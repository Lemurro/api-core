<?php
/**
 * Список
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionIndex
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionIndex extends Action
{
    /**
     * Выполним действие
     *
     * @return array
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run()
    {
        $sets = ORM::for_table('access_sets')
            ->where_null('deleted_at')
            ->find_array();

        if (!is_array($sets)) {
            return Response::error500('При получении наборов произошла ошибка, попробуйте ещё раз');
        }

        $count = count($sets);

        if ($count > 0) {
            foreach ($sets as &$set) {
                if (empty($set['roles'])) {
                    $set['roles'] = [];
                } else {
                    $set['roles'] = json_decode($set['roles'], true);
                }
            }
        }

        return Response::data([
            'count' => $count,
            'items' => $sets,
        ]);
    }
}
