<?php
/**
 * Добавление записи в лог действий
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\DataChangeLogs;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Class Insert
 *
 * @package Lemurro\Api\Core\DataChangeLogs
 */
class Insert extends Action
{
    /**
     * Выполним действие
     *
     * @param string  $table_name  Имя таблицы
     * @param string  $action_name Название действия ('insert'|'update'|'delete')
     * @param integer $record_id   ИД записи
     * @param array   $data        Массив данных
     *
     * @return boolean
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function insert($table_name, $action_name, $record_id, $data = [])
    {
        if (isset($this->di['user']['user_id'])) {
            $user_id = $this->di['user']['user_id'];
        } else {
            $user_id = 0;
        }

        $log = \ORM::for_table('data_change_logs')->create();
        $log->user_id = $user_id;
        $log->table_name = $table_name;
        $log->action_name = $action_name;
        $log->record_id = $record_id;
        $log->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log->created_at = $this->di['datetimenow'];
        $log->save();
        if (is_object($log) && isset($log->id)) {
            return true;
        } else {
            return false;
        }
    }
}
