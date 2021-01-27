<?php

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\App\RunAfter\Users\Get as RunAfterGet;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

class ActionGet extends Action
{
    /**
     * @param integer $id ИД записи
     */
    public function run($id): array
    {
        $record = DB::table('info_users', 'iu')
            ->leftJoin('users', 'users.id', '=', 'iu.user_id')
            ->where('iu.user_id', '=', $id)
            ->whereNull('iu.deleted_at')
            ->first();

        if ($record === null) {
            return Response::error404('Пользователь не найден');
        }

        $record = (array) $record;

        $record['id'] = $record['user_id'];

        if ((string) $record['roles'] !== '') {
            $record['roles'] = json_decode($record['roles'], true);
        } else {
            $record['roles'] = [];
        }

        return (new RunAfterGet($this->dic))->run($record);
    }
}
