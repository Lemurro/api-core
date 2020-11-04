<?php

/**
 * Ротация таблицы data_change_logs
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 30.10.2020
 */

namespace Lemurro\Api\Core\Cron;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Mailer;

/**
 * @package Lemurro\Api\Core\Cron
 */
class DataChangeLogsRotator extends Action
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 30.10.2020
     */
    public function execute(): void
    {
        $step1 = false;
        $step2 = false;

        // Создаем новую таблицу, структуру берём из активной таблицы
        if (DB::statement('CREATE TABLE `data_change_logs_new` LIKE `data_change_logs`')) {
            // Переименовываем активную таблицу в архивную
            $past_year = Carbon::now()->subMonth()->format('Y');
            $step1 = DB::statement('ALTER TABLE `data_change_logs` RENAME `data_change_logs_' . $past_year . '`');

            // Делаем новую таблицу активной
            $step2 = DB::statement('ALTER TABLE `data_change_logs_new` RENAME `data_change_logs`');
        }

        if ($step1 && $step2) {
            $subject = 'DataChangeLogsRotator успешно выполнен';
            $message = 'Успешно выполнена ротация таблицы data_change_logs';
        } else {
            $subject = 'DataChangeLogsRotator не выполнен';
            $message = 'Произошла ошибка при ротации таблицы data_change_logs';
        }

        /** @var Mailer $mailer */
        $mailer = $this->dic['mailer'];
        $mailer->send('simple_message', $subject, $this->dic['config']['cron']['errors_emails'], [
            '[CONTENT]' => $message,
        ]);
    }
}
