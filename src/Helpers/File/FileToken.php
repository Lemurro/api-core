<?php
/**
 * Токены для скачивания файлов
 *
 * @version 15.05.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\Helpers\File;

use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;
use ORM;

/**
 * Class FileToken
 *
 * @package Lemurro\Api\Core\Helpers\File
 */
class FileToken extends Action
{
    /**
     * Создание токена для скачивания файла (с проверкой на дубликаты)
     *
     * @param string $type Тип файла (permanent|temporary)
     * @param string $path Путь до файла
     * @param string $name Имя файла для браузера
     *
     * @return string
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function generate($type, $path, $name)
    {
        $token = md5(uniqid($this->dic['user']['id'], true));

        $record = ORM::for_table('files_downloads')->create();
        $record->type = $type;
        $record->path = $path;
        $record->name = $name;
        $record->token = $token;
        $record->created_at = $this->dic['datetimenow'];
        $record->save();
        if (is_object($record)) {
            return $token;
        } else {
            return '';
        }
    }

    /**
     * Проверим токен и получим путь до файла
     *
     * @param string $token Токен
     *
     * @return array
     *
     * @version 15.05.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function getFileInfo($token)
    {
        $record = ORM::for_table('files_downloads')
            ->select_many(
                'type',
                'path',
                'name',
                'token'
            )
            ->where_equal('token', $token)
            ->find_one();
        if (is_object($record)) {
            if ($record->token === $token) {
                return Response::data([
                    'type' => $record->type,
                    'path' => $record->path,
                    'name' => $record->name,
                ]);
            } else {
                return Response::error400('Неверный токен');
            }
        } else {
            return Response::error404('Токен не найден');
        }
    }
}
