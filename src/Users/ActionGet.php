<?php

namespace Lemurro\Api\Core\Users;

use Lemurro\Api\App\Configs\SettingsPath;
use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

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
        $sql = <<<'SQL'
            SELECT
                iu.*
            FROM info_users AS iu
            LEFT OUTER JOIN users AS u
                ON u.id = iu.user_id
            WHERE iu.user_id = ?
                AND iu.deleted_at IS NULL
            SQL;

        $record = $this->dbal->fetchAssociative($sql, [$id]);
        if ($record === false) {
            return Response::error404('Пользователь не найден');
        }

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
    }
}
