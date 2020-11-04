<?php

/**
 * Просмотр ключей доступа
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Auth;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Auth\Code\Code;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Auth
 */
class ActionGetKeys extends Action
{
    private Code $code_cleaner;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->code_cleaner = new Code($dic['config']['auth']['auth_codes_older_than_hours']);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function run(): array
    {
        $this->code_cleaner->clear();

        $keys = DB::table('auth_codes')
            ->select(
                'code as user_key',
                'user_id',
                'auth_id as user_login'
            )
            ->orderBy('auth_id')
            ->get();

        return Response::data($keys);
    }
}
