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
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Auth
 */
class ActionCheck extends Action
{
    private string $session_id;
    private Session $session;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->session_id = $dic['session_id'];
        $this->session = new Session($dic['config']['auth']);
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 10.09.2020
     */
    public function run(): array
    {
        $session_info = $this->session->check($this->session_id);

        if (isset($session_info['errors'])) {
            return $session_info;
        }

        return Response::data([
            'id' => $session_info['session'],
        ]);
    }
}
