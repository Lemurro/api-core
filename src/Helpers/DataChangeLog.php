<?php
/**
 * Добавление записи в лог действий
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\Core\Abstracts\Action;
use ORM;
use Pimple\Container;

/**
 * Class DataChangeLog
 *
 * @package Lemurro\Api\Core\Helpers
 */
class DataChangeLog extends Action
{
    /**
     * @var array
     */
    protected $user_info;

    /**
     * DataChangeLog constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->user_info = $this->dic['user'];
    }

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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function insert($table_name, $action_name, $record_id, $data = [])
    {
        if (isset($this->user_info['user_id'])) {
            $user_id = $this->user_info['user_id'];
        } else {
            $user_id = 0;
        }

        $log = ORM::for_table('data_change_logs')->create();
        $log->user_id = $user_id;
        $log->table_name = $table_name;
        $log->action_name = $action_name;
        $log->record_id = $record_id;
        $log->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log->created_at = $this->date_time_now;
        $log->save();
        if (is_object($log) && isset($log->id)) {
            return true;
        } else {
            return false;
        }
    }
}
