<?php
/**
 * Добавление
 *
 * @version 05.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\AccessSets;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionInsert
 *
 * @package Lemurro\Api\Core\AccessSets
 */
class ActionInsert extends Action
{
    /**
     * Выполним действие
     *
     * @param array $data Массив данных
     *
     * @return array
     *
     * @version 05.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($data)
    {
        $exist = Exist::check(0, $data['name']);
        if (isset($exist['errors'])) {
            return $exist;
        }

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            $data['roles'] = [];
        }

        $record = ORM::for_table('access_sets')->create();
        $record->name = $data['name'];
        $record->roles = json_encode($data['roles']);
        $record->created_at = $this->dic['datetimenow'];
        $record->save();
        if (is_object($record) && isset($record->id)) {
            $result = $record->as_array();
            $result['roles'] = $data['roles'];

            /** @var DataChangeLog $data_change_log */
            $data_change_log = $this->dic['datachangelog'];
            $data_change_log->insert('access_sets', 'insert', $record->id, $result);

            return Response::data($result);
        } else {
            return Response::error500('Произошла ошибка при добавлении информации о пользователе, попробуйте ещё раз');
        }
    }
}
