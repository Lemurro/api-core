<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * @version 30.10.2020
     */
    public function clear()
    {
        $now = Carbon::now('UTC');
        $older_than = $now->subHours($this->config_file['tokens_older_than_hours'])->toDateTimeString();

        DB::table('files_downloads')
            ->where('created_at', '<', $older_than)
            ->delete();
    }
}
