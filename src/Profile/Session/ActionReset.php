<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 20.01.2021
 */

namespace Lemurro\Api\Core\Profile\Session;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Profile\Session
 */
class ActionReset extends Action
{
    private string $session_id;
    private int $user_id;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->session_id = (string) $dic['session_id'];
        $this->user_id = (int) $dic['user']['id'];
    }

    /**
     * @param string $session
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 20.01.2021
     */
    public function run($session): array
    {
        $record = DB::table('sessions')
            ->where('session', '=', $session)
            ->where('user_id', '=', $this->user_id)
            ->where('admin_entered', '=', 0)
            ->orderByDesc('checked_at')
            ->first();

        if ($record === null) {
            return Response::error404('Сессия не найдена');
        }

        if (
            $record->session !== $session
            || (int) $record->user_id !== $this->user_id
            || (int) $record->admin_entered !== 0
        ) {
            return Response::error404('Найденная сессия не прошла проверку');
        }

        if ($record->session === $this->session_id) {
            return Response::error403('Нельзя завершить активную сессию', false);
        }

        DB::table('sessions')->delete($record->id);

        return Response::data([
            'success' => true,
        ]);
    }
}
