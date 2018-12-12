<?php
/**
 * Список пользователей
 *
 * @version 12.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\Core\Abstracts\Action;
use ORM;

/**
 * Class ActionIndex
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionIndex extends Action
{
    /**
     * @var array
     */
    protected $last_action_dates = [];

    /**
     * @var integer
     */
    protected $info_users_count = 0;

    /**
     * @var array
     */
    protected $info_users_items = [];

    /**
     * Выполним действие
     *
     * @return array
     *
     * @version 27.11.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run()
    {
        $this->getLastActionDates();
        $this->getInfoUsers();

        return [
            'data' => [
                'count' => $this->info_users_count,
                'items' => $this->info_users_items,
            ],
        ];
    }

    /**
     * Получим информацию о датах последних действия
     *
     * @version 11.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function getLastActionDates()
    {
        $sessions = ORM::for_table('sessions')
            ->select_many(
                'user_id',
                'checked_at'
            )
            ->order_by_asc('checked_at')
            ->find_many();
        if (is_array($sessions) && count($sessions) > 0) {
            foreach ($sessions as $session) {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $session->checked_at, SettingsGeneral::TIMEZONE);

                $this->last_action_dates[$session->user_id] = $dt->format('d.m.Y H:i:s');
            }
        }
    }

    /**
     * Получим информацию о пользователях
     *
     * @version 12.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function getInfoUsers()
    {
        $this->info_users_items = ORM::for_table('info_users')
            ->table_alias('iu')
            ->left_outer_join('users', ['u.id', '=', 'iu.user_id'], 'u')
            ->where_null('iu.deleted_at')
            ->order_by_asc('u.auth_id')
            ->find_array();
        if (is_array($this->info_users_items)) {
            $this->info_users_count = count($this->info_users_items);

            if ($this->info_users_count > 0) {
                foreach ($this->info_users_items as &$item) {
                    $item['id'] = $item['user_id'];
                    $item['last_action_date'] = (isset($this->last_action_dates[$item['user_id']]) ? $this->last_action_dates[$item['user_id']] : 'отсутствует');
                }
            }
        } else {
            $this->info_users_items = [];
        }
    }
}
