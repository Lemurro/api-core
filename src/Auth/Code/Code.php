<?php

namespace Lemurro\Api\Core\Auth\Code;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Lemurro\Api\App\Configs\SettingsAuth;

/**
 * Очистка устаревших кодов аутентификации
 */
class Code
{
    protected Connection $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Очистка устаревших кодов аутентификации
     */
    public function clear(?string $auth_id = null): void
    {
        $this->dbal->executeStatement('DELETE FROM auth_codes WHERE created_at <= ?', [
            Carbon::now('UTC')->subHours(SettingsAuth::AUTH_CODES_OLDER_THAN)->toDateTimeString(),
        ]);

        if (!empty($auth_id)) {
            $this->dbal->delete('auth_codes', ['auth_id' => $auth_id]);
        }
    }
}
