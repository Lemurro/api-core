<?php

/**
 * Ротация таблицы data_change_logs
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 01.12.2020
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
    private string $new_name = 'data_change_logs_new';
    private string $old_name = 'data_change_logs';

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 01.12.2020
     */
    public function execute(): void
    {
        $step1 = false;
        $step2 = false;

        // Создаем новую таблицу, структуру берём из активной таблицы
        if (DB::statement($this->getCreateSql())) {
            // Переименовываем активную таблицу в архивную
            $past_year = Carbon::now()->subMonth()->format('Y');
            $step1 = DB::statement("ALTER TABLE $this->old_name RENAME TO $this->old_name_$past_year");

            // Делаем новую таблицу активной
            $step2 = DB::statement("ALTER TABLE $this->new_name RENAME TO $this->old_name");
        }

        if ($step1 && $step2) {
            $subject = 'DataChangeLogsRotator успешно выполнен';
            $message = "Успешно выполнена ротация таблицы $this->old_name";
        } else {
            $subject = 'DataChangeLogsRotator не выполнен';
            $message = "Произошла ошибка при ротации таблицы $this->old_name";
        }

        /** @var Mailer $mailer */
        $mailer = $this->dic['mailer'];
        $mailer->send('simple_message', $subject, $this->dic['config']['cron']['errors_emails'], [
            '[CONTENT]' => $message,
        ]);
    }

    private function getCreateSql(): string
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return "CREATE TABLE $this->new_name AS TABLE $this->old_name WITH NO DATA";
        }

        return "CREATE TABLE $this->new_name LIKE $this->old_name";
    }
}
