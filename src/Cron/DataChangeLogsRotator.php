<?php

namespace Lemurro\Api\Core\Cron;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsCron;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Mailer;

/**
 * Ротация таблицы data_change_logs
 */
class DataChangeLogsRotator extends Action
{
    /**
     * Выполним ротацию
     */
    public function execute()
    {
        $step1 = false;
        $step2 = false;

        // Создаем новую таблицу, структуру берём из активной таблицы
        if ($this->dbal->executeStatement('CREATE TABLE data_change_logs_new LIKE data_change_logs')) {
            // Переименовываем активную таблицу в архивную
            $past_year = Carbon::now()->subMonth()->format('Y');
            $step1 = $this->dbal->executeStatement('ALTER TABLE data_change_logs RENAME data_change_logs_' . $past_year);

            // Делаем новую таблицу активной
            $step2 = $this->dbal->executeStatement('ALTER TABLE data_change_logs_new RENAME data_change_logs');
        }

        if ($step1 && $step2) {
            $subject = 'DataChangeLogsRotator выполнена успешно';
            $message = 'Успешно выполнена ротация таблицы data_change_logs';
        } else {
            $subject = 'DataChangeLogsRotator не выполнена';
            $message = 'Произошла ошибка при ротации таблицы data_change_logs';
        }

        /** @var Mailer $mailer */
        $mailer = $this->dic['mailer'];
        $mailer->send('SIMPLE_MESSAGE', $subject, SettingsCron::ERRORS_EMAILS, [
            '[CONTENT]' => $message,
        ]);
    }
}
