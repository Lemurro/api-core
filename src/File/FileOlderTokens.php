<?php
/**
 * Очистим устаревшие токены для скачивания
 *
 * @version 08.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\File;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsFile;
use ORM;

/**
 * Class FileOlderTokens
 *
 * @package Lemurro\Api\Core\File
 */
class FileOlderTokens
{
    /**
     * Выполним очистку
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function clear()
    {
        $now = Carbon::now('UTC');

        ORM::for_table('files_downloads')
            ->where_lt('created_at', $now->subHours(SettingsFile::TOKENS_OLDER_THAN_HOURS))
            ->delete_many();
    }
}
