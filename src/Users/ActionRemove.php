<?php
/**
 * Удаление пользователя
 *
 * @version 29.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\RunAfter\Users\Remove as RunAfterRemove;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\DataChangeLog\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function run($id)
    {
        $user = ORM::for_table('users')
            ->find_one($id);
        if (is_object($user)) {
            $info = ORM::for_table('info_users')
                ->where_equal('user_id', $id)
                ->find_one();
            if (is_object($info)) {
                $info->deleted_at = $this->dic['datetimenow'];
                $info->save();
                if (is_object($info) && isset($info->id)) {
                    ORM::for_table('auth_codes')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    ORM::for_table('sessions')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    $user->delete();

                    /** @var DataChangeLog $data_change_log */
                    $data_change_log = $this->dic['datachangelog'];
                    $data_change_log->insert('users', 'delete', $id);

                    return (new RunAfterRemove($this->dic))->run([
                        'id' => $id,
                    ]);
                } else {
                    return Response::error500('Произошла ошибка при удалении пользователя, попробуйте ещё раз');
                }
            } else {
                return Response::error404('Информация о пользователе не найдена');
            }
        } else {
            return Response::error404('Пользователь не найден');
        }
    }
}
