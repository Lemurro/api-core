<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Session;

/**
 * @package Lemurro\Api\Core\Auth
 */
class ActionCheck extends Action
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function run(): array
    {
        $result_session_check = (new Session())->check($this->dic['session_id']);
        if (isset($result_session_check['errors'])) {
            return $result_session_check;
        }

        return Response::data([
            'id' => $result_session_check['session'],
        ]);
    }
}
