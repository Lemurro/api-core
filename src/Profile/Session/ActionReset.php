<?php

namespace Lemurro\Api\Core\Profile\Session;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * Сброс выбранной сессии
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
     * @param Container $dic
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->session_id = $dic['session_id'];
        $this->user_id = $dic['user']['id'];
    }

    /**
     * Сброс выбранной сессии
     *
     * @param string $session
     *
     * @return array
     */
    public function run($session): array
    {
        $sql = <<<'SQL'
            SELECT * FROM sessions
            WHERE session = :session
                AND user_id = :user_id
                AND admin_entered = '0'
            ORDER BY checked_at DESC
            SQL;

        $record = $this->dbal->fetchAssociative($sql, [
            'session' => $session,
            'user_id' => $this->user_id,
        ]);
        if ($record === false) {
            Response::error404('Сессия не найдена');
        }

        if (
            $record['session'] === $session
            && $record['user_id'] === $this->user_id
            && (int)$record['admin_entered'] === 0
        ) {
            Response::error404('Сессия не найдена');
        }

        if ($record['session'] === $this->session_id) {
            Response::error403('Нельзя завершить активную сессию', false);
        }

        $this->dbal->delete('sessions', ['session' => $session]);

        return Response::data([
            'success' => true,
        ]);
    }
}
