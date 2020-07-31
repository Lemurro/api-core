<?php

/**
 * Удаление пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 31.07.2020
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Remove as RunAfterRemove;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use ORM;
use Throwable;

/**
 * Class ActionRemove
 *
 * @package Lemurro\Api\Core\Users
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
     *
     * @version 31.07.2020
     */
    public function run($id)
    {
        $user = ORM::for_table('users')
            ->where_null('deleted_at')
            ->find_one($id);
        if (is_object($user) && $user->id == $id) {
            if ($id == 1) {
                return Response::error403('Пользователь с id=1 не может быть удалён', false);
            }

            $info = ORM::for_table('info_users')
                ->where_equal('user_id', $id)
                ->find_one();
            if (is_object($info) && $info->user_id == $id) {
                try {
                    ORM::get_db()->beginTransaction();

                    $user->deleted_at = $this->dic['datetimenow'];
                    $user->save();

                    $info->deleted_at = $this->dic['datetimenow'];
                    $info->save();

                    ORM::for_table('auth_codes')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    ORM::for_table('sessions')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    ORM::get_db()->commit();
                } catch (Throwable $t) {
                    ORM::get_db()->rollBack();

                    LogException::write($this->dic['log'], $t);

                    return Response::error500('Произошла ошибка при удалении пользователя, попробуйте ещё раз');
                }

                /** @var DataChangeLog $data_change_log */
                $data_change_log = $this->dic['datachangelog'];
                $data_change_log->insert('users', 'delete', $id);

                return (new RunAfterRemove($this->dic))->run([
                    'id' => $id,
                ]);
            } else {
                return Response::error404('Информация о пользователе не найдена');
            }
        } else {
            return Response::error404('Пользователь не найден');
        }
    }
}
