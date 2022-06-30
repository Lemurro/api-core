<?php

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Session;

class ActionCheck extends Action
{
    public function run(): array
    {
        $result_session_check = (new Session($this->dbal))->check((string)$this->dic['session_id']);
        if (isset($result_session_check['errors'])) {
            return $result_session_check;
        }

        return Response::data([
            'id' => $result_session_check['session'],
        ]);
    }
}
