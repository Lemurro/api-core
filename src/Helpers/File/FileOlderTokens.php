<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 25.09.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsFile;
use ORM;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileOlderTokens
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 25.09.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        ORM::for_table('files_downloads')
            ->where_lt('created_at', $now->subHours(SettingsFile::$tokens_older_than_hours))
            ->delete_many();
    }
}
