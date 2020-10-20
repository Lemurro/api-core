<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use ORM;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileOlderTokens
{
    private array $config_file;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_file)
    {
        $this->config_file = $config_file;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        ORM::for_table('files_downloads')
            ->where_lt('created_at', $now->subHours($this->config_file['tokens_older_than_hours']))
            ->delete_many();
    }
}
