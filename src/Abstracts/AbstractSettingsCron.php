<?php

/**
 * Параметры cron-задач
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
abstract class AbstractSettingsCron
{
    /**
     * Префикс для имён заданий
     *
     * В случае, когда у вас на одном сервере несколько проектов, имена задач обязательно должны отличаться,
     * иначе это приводит к конфликтам при запуске задач
     */
    public static string $name_prefix = 'MyApp1';

    /**
     * Путь до лог-файла
     */
    public static string $log_file = SettingsPath::$logs . 'cron.log';

    /**
     * Массив email-адресов, куда отправлять письма с ошибками
     */
    public static array $errors_emails = [];

    /**
     * Выполнять (true) или нет (false) cron-задачу: Очистка устаревших токенов для скачивания файлов
     */
    public static bool $file_older_tokens_enabled = true;

    /**
     * Выполнять (true) или нет (false) cron-задачу: Очистка устаревших файлов во временном каталоге
     */
    public static bool $file_older_files_enabled = true;

    /**
     * Выполнять (true) или нет (false) cron-задачу: Ротация таблицы data_change_logs
     */
    public static bool $data_change_logs_rotator_enabled = true;
}
