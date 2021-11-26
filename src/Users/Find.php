<?php

namespace Lemurro\Api\Core\Users;

use Illuminate\Support\Facades\DB;

class Find
{
    /**
     * @param string $auth_id Номер телефона или электронная почта
     */
    public function run($auth_id): array
    {
        $user = DB::table('users')
            ->where('auth_id', '=', $auth_id)
            ->whereNull('deleted_at')
            ->first();

        if ($user === null) {
            return [];
        }

        if (mb_strtolower($user->auth_id, 'UTF-8') === mb_strtolower($auth_id, 'UTF-8')) {
            return (array) $user;
        }
    }
}
