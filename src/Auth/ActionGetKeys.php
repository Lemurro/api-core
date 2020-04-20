<?php
/**
 * Просмотр ключей доступа
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 17.04.2020
 */

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Auth\Code\Code;
use Lemurro\Api\Core\Helpers\Response;
use ORM;
use Pimple\Container;

/**
 * Class ActionGetKeys
 *
 * @package Lemurro\Api\Core\Auth
 */
class ActionGetKeys extends Action
{
    /**
     * @var Code
     */
    private $code_cleaner;

    /**
     * ActionGetKeys constructor.
     *
     * @param Container $dic
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->code_cleaner = new Code();
    }

    /**
     * Выполним действие
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public function run(): array
    {
        $this->code_cleaner->clear();

        $keys = ORM::for_table('auth_codes')
            ->select('code', 'user_key')
            ->select('user_id', 'user_id')
            ->select('auth_id', 'user_login')
            ->order_by_asc('auth_id')
            ->find_array();

        if (!is_array($keys)) {
            $keys = [];
        }

        return Response::data($keys);
    }
}
