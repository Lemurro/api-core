<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRemove extends Action
{
    protected FileInfo $file_info;
    protected FileRights $file_rights;

    /**
     * @param Container $dic Контейнер
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->file_info = new FileInfo();
        $this->file_rights = new FileRights($dic);
    }

    /**
     * @param integer $fileid ИД файла
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function run($fileid)
    {
        $info = $this->file_info->getOneORM($fileid);
        if (is_array($info)) {
            return $info;
        }

        if ($this->file_rights->check($info->container_type, $info->container_id) === false) {
            return Response::error403('Доступ ограничен', false, [
                'file_id' => $fileid,
            ]);
        }

        $file_path = $this->dic['config']['file']['path_upload'] . '/' . $info->path;

        if ($this->dic['config']['file']['full_remove']) {
            $info->delete();
        } else {
            $info->deleted_at = $this->datetimenow;
            $info->save();
        }

        if (is_object($info)) {
            /** @var DataChangeLog $datachangelog */
            $datachangelog = $this->dic['datachangelog'];
            $datachangelog->insert('files', $datachangelog::ACTION_DELETE, $info->id, $info->as_array());

            if ($this->dic['config']['file']['full_remove']) {
                @unlink($file_path);
            }

            return Response::data([
                'id' => $fileid,
            ]);
        } else {
            return Response::error500('Файл не был удалён, попробуйте ещё раз', [
                'file_id' => $fileid,
            ]);
        }
    }
}
