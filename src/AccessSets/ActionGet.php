<?php
/**
 * Получение
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ActionGet
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionGet extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id ИД записи
     *
     * @return array
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id)
    {
        $record = OneRecord::get($id);
        if (!is_object($record)) {
            return Response::error404('Набор не найден');
        }

        $record = $record->as_array();

        if (empty($record['roles'])) {
            $record['roles'] = [];
        } else {
            $record['roles'] = json_decode($record['roles'], true);
        }

        return Response::data($record);
    }
}
