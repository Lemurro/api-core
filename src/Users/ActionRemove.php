<?php
/**
 * Удаление пользователя
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Users;

use Exception;
use Lemurro\Api\App\RunAfter\Users\Remove as RunAfterRemove;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Monolog\Logger;
use ORM;
use Pimple\Container;

/**
 * Class ActionRemove
 *
 * @package Lemurro\Api\Core\Users
 */
class ActionRemove extends Action
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * ActionRemove constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->log = $dic['log'];
    }

    /**
     * Выполним действие
     *
     * @param integer $id ИД записи
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
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

                    $user->deleted_at = $this->date_time_now;
                    $user->save();

                    $info->deleted_at = $this->date_time_now;
                    $info->save();

                    ORM::for_table('auth_codes')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    ORM::for_table('sessions')
                        ->where_equal('user_id', $id)
                        ->delete_many();

                    ORM::get_db()->commit();
                } catch (Exception $e) {
                    ORM::get_db()->rollBack();

                    LogException::write($this->log, $e);

                    return Response::error500('Произошла ошибка при удалении пользователя, попробуйте ещё раз');
                }

                $this->data_change_log->insert('users', 'delete', $id);

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
