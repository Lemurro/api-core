<?php

namespace Lemurro\Api\Core\Auth;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Auth\Code\Code;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * Просмотр ключей доступа
 */
class ActionGetKeys extends Action
{
    /**
     * @var Code
     */
    private $code_cleaner;

    /**
     * @param Container $dic
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->code_cleaner = new Code($this->dbal);
    }

    /**
     * Просмотр ключей доступа
     *
     * @return array
     */
    public function run(): array
    {
        $this->code_cleaner->clear();

        $sql = <<<'SQL'
            SELECT
                code AS user_key,
                user_id AS user_id,
                auth_id AS user_login
            FROM auth_codes
            ORDER BY auth_id ASC
            SQL;

        return Response::data((array)$this->dbal->fetchAllAssociative($sql));
    }
}
