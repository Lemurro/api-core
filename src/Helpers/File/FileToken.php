<?php

/**
 * Токены для скачивания файлов
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Helpers\File;

use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Helpers\Response;
use Lemurro\Api\Core\Abstracts\Action;

/**
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
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function generate($type, $path, $name): string
    {
        $token = md5(uniqid($this->dic['user']['id'], true));

        $inserted = DB::table('files_downloads')->insert([
            'type' => $type,
            'path' => $path,
            'name' => $name,
            'token' => $token,
            'created_at' => $this->datetimenow,
        ]);

        return $inserted ? $token : '';
    }

    /**
     * Проверим токен и получим путь до файла
     *
     * @param string $token Токен
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function getFileInfo($token): array
    {
        $record = DB::table('files_downloads')
            ->select(
                'type',
                'path',
                'name',
                'token'
            )
            ->where('token', '=', $token)
            ->first();

        if ($record === null) {
            return Response::error404('Токен не найден');
        }

        if ($record->token === $token) {
            return Response::data([
                'type' => $record->type,
                'path' => $record->path,
                'name' => $record->name,
            ]);
        }

        return Response::error400('Неверный токен');
    }
}
