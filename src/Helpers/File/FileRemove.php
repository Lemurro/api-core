<?php

namespace Lemurro\Api\Core\Helpers\File;

use Doctrine\DBAL\Exception;
use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\DataChangeLog;
use Lemurro\Api\Core\Helpers\Response;
use Pimple\Container;
use Throwable;

/**
 * Удаление файла
 */
class FileRemove extends Action
{
    /**
     * @var FileInfo
     */
    protected FileInfo $file_info;

    /**
     * @var FileRights
     */
    protected FileRights $file_rights;

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
     * @param integer|string $file_id ИД файла
     *
     * @return array
     * @throws Throwable
     */
    public function run(int|string $file_id): array
    {
        $info = $this->file_info->getById($file_id);

        if (empty($info)) {
            return Response::error404('Файл не найден');
        }

        if ($this->file_rights->check($info['container_type'], $info['container_id']) === false) {
            return Response::error403('Доступ ограничен', false, [
                'file_id' => $file_id,
            ]);
        }

        if ($this->isTransactionActive()) {
            $this->removeFile($file_id, $info);
        } else {
            $this->dbal->transactional(function () use ($file_id, $info): void {
                $this->removeFile($file_id, $info);
            });
        }

        return Response::data([
            'id' => $file_id,
        ]);
    }

    /**
     * Выполняет удаление файла.
     *
     * Метод не управляет транзакцией сам.
     * Транзакция открывается выше только если она ещё не открыта.
     *
     * @param integer|string $file_id ИД файла
     * @param array $info Данные файла до удаления
     *
     * @return void
     * @throws Exception
     */
    protected function removeFile(int|string $file_id, array $info): void
    {
        $file_path = SettingsFile::FILE_FOLDER . $info['path'];

        if (SettingsFile::FULL_REMOVE) {
            $this->dbal->delete('files', [
                'id' => $file_id,
            ]);

            @unlink($file_path);
        } else {
            $this->dbal->update('files', [
                'deleted_at' => $this->dic['datetimenow'],
            ], [
                'id' => $file_id,
            ]);
        }

        /** @var DataChangeLog $datachangelog */
        $datachangelog = $this->dic['datachangelog'];
        $datachangelog->insert('files', 'delete', $file_id, $info);
    }

    /**
     * Проверяет, открыта ли уже транзакция.
     *
     * Сначала проверяем нативное PDO-соединение, потому что внешняя транзакция
     * может быть открыта через ORM::get_db()->beginTransaction(), а не через DBAL.
     *
     * @return boolean
     */
    protected function isTransactionActive(): bool
    {
        if (method_exists($this->dbal, 'getNativeConnection')) {
            $connection = $this->dbal->getNativeConnection();

            if (is_object($connection) && method_exists($connection, 'inTransaction')) {
                return $connection->inTransaction();
            }
        }

        if (method_exists($this->dbal, 'isTransactionActive')) {
            return $this->dbal->isTransactionActive();
        }

        return false;
    }
}
