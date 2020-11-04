<?php

/**
 * Добавление записи в лог действий
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;

/**
 * @package Lemurro\Api\Core\Helpers
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
     * @param string  $table_name  Имя таблицы
     * @param string  $action_name Название действия ('insert'|'update'|'delete')
     * @param integer $record_id   ИД записи
     * @param array   $data        Массив данных
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function insert($table_name, $action_name, $record_id, $data = []): bool
    {
        return DB::table('data_change_logs')->insert([
            'user_id' => $this->dic['user']['user_id'] ?? 0,
            'table_name' => $table_name,
            'action_name' => $action_name,
            'record_id' => $record_id,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'created_at' => $this->datetimenow,
        ]);
    }
}
