<?php
/**
 * Изменение
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Class ActionSave
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionSave extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id   ИД записи
     * @param array   $data Массив данных
     *
     * @return array
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id, $data)
    {
        $record = OneRecord::get($id);
        if (!is_object($record)) {
            return Response::error404('Набор не найден');
        }

        $exist = Exist::check($id, $data['name']);
        if (isset($exist['errors'])) {
            return $exist;
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $record->name = $data['name'];
        $record->roles = json_encode($data['roles']);
        $record->updated_at = $this->dic['datetimenow'];
        $record->save();
        if (is_object($record) && isset($record->id)) {
            $result = $record->as_array();
            $result['roles'] = $data['roles'];

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', 'update', $id, $result);

            return Response::data($result);
        } else {
            return Response::error500('Произошла ошибка при изменении набора, попробуйте ещё раз');
        }
    }
}
