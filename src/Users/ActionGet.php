<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;

/**
 * Получение пользователя
 */
class ActionGet extends Action
{
    /**
     * Получение пользователя
     *
     * @param integer $id ИД записи
     *
     * @return array
     */
    public function run($id)
    {
        $record = ORM::for_table('info_users')
            ->table_alias('iu')
            ->left_outer_join('users', ['u.id', '=', 'iu.user_id'], 'u')
            ->where_equal('iu.user_id', $id)
            ->where_null('iu.deleted_at')
            ->find_one();
        if (is_object($record) && $record->user_id == $id) {
            $record = $record->as_array();

            $record['id'] = $record['user_id'];

            if ($record['roles'] != '') {
                $record['roles'] = json_decode($record['roles'], true);
            } else {
                $record['roles'] = [];
            }

            // last app version
            $record['last_app_version'] = '0';
            $file_path = SettingsPath::FULL_ROOT . 'version.last';
            if (is_file($file_path) && is_readable($file_path)) {
                $last_app_version = file_get_contents($file_path);
                if (empty($last_app_version) === false) {
                    $record['last_app_version'] = trim($last_app_version);
                }
            }

            return (new RunAfterGet($this->dic))->run($record);
        } else {
            return Response::error404('Пользователь не найден');
        }
    }
}
