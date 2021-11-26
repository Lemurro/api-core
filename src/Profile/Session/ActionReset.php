<?php

/**
 * Сброс выбранной сессии
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 11.05.2020
 */

namespace Lemurro\Api\Core\Profile\Session;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use ORM;
use Pimple\Container;

/**
 * Class ActionReset
 *
 * @package Lemurro\Api\Core\Profile\Session
 */
class ActionReset extends Action
{
    /**
     * @var string
     */
    private $session_id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * ActionIndex constructor.
     *
     * @param Container $dic
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 11.05.2020
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->session_id = $dic['session_id'];
        $this->user_id = $dic['user']['id'];
    }

    /**
     * Выполним действие
     *
     * @param string $session
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 11.05.2020
     */
    public function run($session): array
    {
        $record = ORM::for_table('sessions')
            ->where_equal('session', $session)
            ->where_equal('user_id', $this->user_id)
            ->where_equal('admin_entered', '0')
            ->order_by_desc('checked_at')
            ->find_one();

        if (!is_object($record)) {
            Response::error404('Сессия не найдена');
        }

        if (
            $record->session === $session
            && $record->user_id === $this->user_id
            && (int) $record->admin_entered === 0
        ) {
            Response::error404('Сессия не найдена');
        }

        if ($record->session === $this->session_id) {
            Response::error403('Нельзя завершить активную сессию', false);
        }

        $record->delete();

        return Response::data([
            'success' => true,
        ]);
    }
}
