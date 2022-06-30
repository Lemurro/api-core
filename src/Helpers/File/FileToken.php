<?php

namespace Lemurro\Api\Core\Helpers\File;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Lemurro\Api\App\Configs\SettingsFile;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Response;

/**
 * Токен для скачивания файлов
 */
class FileToken extends Action
{
    /**
     * @var string Алгоритм шифрования подписи
     */
    static protected string $alg = 'HS256';

    /**
     * Создание токена для скачивания файла
     *
     * @param string $type      Тип файла (permanent|temporary)
     * @param string $file_path Путь до файла
     * @param string $file_name Имя файла для браузера
     */
    public function generate(string $type, string $file_path, string $file_name): string
    {
        $now = Carbon::now();

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->addHours(SettingsFile::TOKENS_OLDER_THAN_HOURS)->getTimestamp(),
            'ltp' => $type,
            'lfp' => $file_path,
            'lfn' => $file_name,
        ];

        return JWT::encode($payload, SettingsFile::SECRET_KEY_FOR_TOKENS, self::$alg);
    }

    /**
     * Проверим токен и получим путь до файла
     */
    public function getFileInfo(string $jwt_token): array
    {
        $payload = JWT::decode($jwt_token, new Key(SettingsFile::SECRET_KEY_FOR_TOKENS, self::$alg), [self::$alg]);

        return Response::data([
            'type' => $payload->ltp,
            'path' => $payload->lfp,
            'name' => $payload->lfn,
        ]);
    }
}
