<?php
/**
 * Заблокировать \ Разблокировать пользователя
 *
 * @version 03.06.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\LockUnlock as RunAfterLockUnlock;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Class ActionLockUnlock
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionLockUnlock extends Action
{
    /**
     * Выполним действие
     *
     * @param integer $id     ИД записи
     * @param boolean $locked Статус блокировки (true|false)
     *
     * @return array
     *
     * @version 03.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id, $locked)
    {
        $user = ORM::for_table('users')
            ->find_one($id);
        if (is_object($user)) {
            if ($id == 1 && $locked) {
                return Response::error403('Пользователь с id=1 не может быть заблокирован', false);
            }

            $user_info = (new ActionGet($this->dic))->run($id);
            if (isset($user_info['errors'])) {
                return $user_info;
            }

            $user->locked = $locked;
            $user->save();
            if (is_object($user) && isset($user->id)) {
                /** @var DataChangeLog $data_change_log */
                $data_change_log = $this->dic['datachangelog'];
                $data_change_log->insert('users', 'update', $id, $user->as_array());

                $user_info['data']['locked'] = $locked;

                if ($locked) {
                    ORM::for_table('sessions')
                        ->where_equal('user_id', $id)
                        ->delete_many();
                }

                return (new RunAfterLockUnlock($this->dic))->run($user_info['data']);
            } else {
                return Response::error500('Произошла ошибка при изменении статуса блокировки пользователя, попробуйте ещё раз');
            }
        } else {
            return Response::error404('Пользователь не найден');
        }
    }
}