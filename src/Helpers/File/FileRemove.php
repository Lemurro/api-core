<?php
/**
 * Удаление файла
 *
 * @version 28.03.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;

/**
 * Class FileRemove
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileRemove extends Action
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
     * Конструктор
     *
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
     * Выполним действие
     *
     * @param integer $fileid ИД файла
     *
     * @return array
     *
     * @version 08.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
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

        $file_path = SettingsFile::FILE_FOLDER . $info->path;

        if (SettingsFile::FULL_REMOVE) {
            $info->delete();
        } else {
            $info->deleted_at = $this->dic['datetimenow'];
            $info->save();
        }

        if (is_object($info)) {
            /** @var DataChangeLog $datachangelog */
            $datachangelog = $this->dic['datachangelog'];
            $datachangelog->insert('files', 'delete', $info->id, $info->as_array());

            if (SettingsFile::FULL_REMOVE) {
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
