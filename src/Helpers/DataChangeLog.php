<?php

namespace Lemurro\Api\Core\Helpers;

use Lemurro\Api\Core\Abstracts\Action;

/**
 * Добавление записи в лог действий
 */
class DataChangeLog extends Action
{
    /**
     * @var string
     */
    public const ACTION_INSERT = 'insert';

    /**
     * @var string
     */
    public const ACTION_UPDATE = 'update';

    /**
     * @var string
     */
    public const ACTION_DELETE = 'delete';

    /**
     * Добавление записи в лог действий
     *
     * @param string  $table_name  Имя таблицы
     * @param string  $action_name Название действия ('insert'|'update'|'delete')
     * @param integer $record_id   ИД записи
     * @param array   $data        Массив данных
     *
     * @return boolean
     */
    public function insert($table_name, $action_name, $record_id, $data = [])
    {
        if (isset($this->dic['user']['user_id'])) {
            $user_id = $this->dic['user']['user_id'];
        } else {
            $user_id = 0;
        }

        $cnt = $this->dbal->insert('data_change_logs', [
            'user_id' => $user_id,
            'table_name' => $table_name,
            'action_name' => $action_name,
            'record_id' => $record_id,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'created_at' => $this->dic['datetimenow'],
        ]);
        if ($cnt !== 1) {
            return false;
        }

        return true;
    }
}
