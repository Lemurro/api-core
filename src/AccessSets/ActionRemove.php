<?php
/**
 * Удаление
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ActionRemove
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionRemove extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id ИД записи
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function run($id)
    {
        $record = OneRecord::get($id);
        if (!is_object($record)) {
            return Response::error404('Набор не найден');
        }

        $record->deleted_at = $this->date_time_now;
        $record->save();
        if (is_object($record) && isset($record->id)) {
            $this->data_change_log->insert('access_sets', 'delete', $id);

            return Response::data([
                'id' => $id,
            ]);
        } else {
            return Response::error500('Произошла ошибка при удалении набора, попробуйте ещё раз');
        }
    }
}
