<?php

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Получим номер версии приложения
 */
class ControllerGet extends Controller
{
    public function start()
    {
        $this->response->setData(
            (new ActionGet())->run()
        )->send();
    }
}
