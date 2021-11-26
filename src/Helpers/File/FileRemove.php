<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 04.11.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRemove extends Action
{
    protected FileRights $file_rights;

    /**
     * @param Container $dic Контейнер
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 04.11.2020
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->file_rights = new FileRights($dic);
    }

    /**
     * @param integer $fileid ИД файла
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 04.11.2020
     */
    public function run($fileid): array
    {
        $info = DB::table('files')
            ->where('id', '=', $fileid)
            ->whereNull('deleted_at')
            ->first();
        if ($info === null) {
            return Response::error404('Файл не найден');
        }

        if ($this->file_rights->check($info->container_type, $info->container_id) === false) {
            return Response::error403('Доступ ограничен', false, [
                'file_id' => $fileid,
            ]);
        }

        $file_path = $this->dic['config']['file']['path_upload'] . '/' . $info->path;

        if ($this->dic['config']['file']['full_remove']) {
            if (DB::table('files')->delete($fileid) === 0) {
                return Response::error500('Файл не был удалён, попробуйте ещё раз', [
                    'file_id' => $fileid,
                ]);
            }
        } else {
            $affected = DB::table('files')
                ->where('id', '=', $fileid)
                ->update([
                    'deleted_at' => $this->datetimenow,
                ]);

            if ($affected === 0) {
                return Response::error500('Файл не был удалён, попробуйте ещё раз', [
                    'file_id' => $fileid,
                ]);
            }
        }

        /** @var DataChangeLog $datachangelog */
        $datachangelog = $this->dic['datachangelog'];
        $datachangelog->insert('files', $datachangelog::ACTION_DELETE, $info->id, (array) $info);

        if ($this->dic['config']['file']['full_remove']) {
            @unlink($file_path);
        }

        return Response::data([
            'id' => $fileid,
        ]);
    }
}
