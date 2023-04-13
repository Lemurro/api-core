<?php

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\LogException;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;
use Throwable;

/**
 * Удаление файла
 */
class FileRemove extends AbstractFileAction
{
    /**
     * @var FileInfo
     */
    protected $file_info;

    /**
     * @var FileRights
     */
    protected $file_rights;

    /**
     * @param Container $dic Контейнер
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->file_info = new FileInfo($this->dbal);
        $this->file_rights = new FileRights($dic);
    }

    /**
     * Удаление файла
     *
     * @param integer $fileid ИД файла
     *
     * @return array
     */
    public function run($fileid)
    {
        $info = $this->file_info->getById($fileid);
        if (empty($info)) {
            return Response::error404('Файл не найден');
        }

        if ($this->file_rights->check($info['container_type'], $info['container_id']) === false) {
            return Response::error403('Доступ ограничен', false, [
                'file_id' => $fileid,
            ]);
        }

        $file_path = SettingsFile::FILE_FOLDER . $info['path'];

        /** @psalm-suppress RedundantCondition */
        if (SettingsFile::FULL_REMOVE) {
            $this->dbal->delete('files', ['id' => $fileid]);
            @unlink($file_path);
        } else {
            $this->dbal->update('files', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'id' => $fileid
            ]);
        }

        try {
            /** @var DataChangeLog $datachangelog */
            $datachangelog = $this->dic['datachangelog'];
            $datachangelog->insert('files', 'delete', $fileid, $info);
        } catch (Throwable $th) {
            LogException::write($this->log, $th);
        }

        return Response::data([
            'id' => $fileid,
        ]);
    }
}
