<?php

/**
 * Параметры загрузки файлов
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\App\Configs\SettingsPath;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsFile
{
    /**
     * Путь до каталога upload
     */
    public static string $root_folder = SettingsPath::$root . 'upload/';

    /**
     * Путь до временного хранилища
     */
    public static string $temp_folder = self::$root_folder . 'temp/';

    /**
     * Путь до постоянного хранилища
     */
    public static string $file_folder = self::$root_folder . 'documents/';

    /**
     * Полное удаление файлов
     *   true - файл удаляется физически, а также удаляется запись в БД
     *   false - файл физически не удаляется, а в БД помечается как удалённый
     */
    public static bool $full_remove = false;

    /**
     * Через сколько дней временный файл считать устаревшим
     */
    public static int $outdated_file_days = 5;

    /**
     * Через сколько часов токен на скачивание файла считать устаревшим
     */
    public static int $tokens_older_than_hours = 12;

    /**
     * Максимальный размер загружаемого файла (в байтах)
     */
    public static int $allowed_size = 2097152; // 2 MB

    /**
     * Формат сообщения о превышении лимита размера загружаемого файла
     */
    public static string $max_size_formated = '2 MB';

    /**
     * Режим проверки файла: по типу содержимого (type) или по расширению (ext)
     */
    public static string $check_file_by = 'type';

    /**
     * Массив разрешенных типов
     */
    public static array $allowed_types = [
        'application/pdf', // pdf
        'application/msword', // doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        'application/vnd.ms-excel', // xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/zip', // zip
        'application/x-rar', // rar
    ];

    /**
     * Массив разрешенных расширений
     */
    public static array $allowed_extensions = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'zip',
        'rar',
    ];
}
