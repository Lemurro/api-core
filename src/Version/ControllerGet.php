<?php
/**
 * Получим номер последней версии приложения
 *
 * @version 26.05.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Version;

use Lemurro\Api\Core\Abstracts\Controller;

/**
 * Class ControllerGet
 *
 * @package Lemurro\Api\Core\Version
 */
class ControllerGet extends Controller
{
    /**
     * Стартовый метод
     *
     * @version 26.05.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function start()
    {
        $result = (new ActionGet())->run();

        $this->response->setData($result);
        $this->response->send();
    }
}
