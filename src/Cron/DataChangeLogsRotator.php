<?php
/**
 * Ротация таблицы data_change_logs
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 19.11.2019
 */

namespace Lemurro\Api\Core\Cron;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsCron;
use Lemurro\Api\Core\Abstracts\Action;
use Lemurro\Api\Core\Helpers\Mailer;
use ORM;
use Pimple\Container;

/**
 * Class DataChangeLogsRotator
 *
 * @package Lemurro\Api\Core\Cron
 */
class DataChangeLogsRotator extends Action
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * FileToken constructor.
     *
     * @param Container $dic Объект контейнера зависимостей
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function __construct($dic)
    {
        parent::__construct($dic);

        $this->mailer = $this->dic['mailer'];
    }

    /**
     * Выполним ротацию
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 19.11.2019
     */
    public function execute()
    {
        $step1 = false;
        $step2 = false;

        // Создаем новую таблицу, структуру берём из активной таблицы
        if (ORM::raw_execute('CREATE TABLE `data_change_logs_new` SELECT * FROM `data_change_logs` WHERE 1=0')) {
            // Переименовываем активную таблицу в архивную
            $past_year = Carbon::now()->subMonth()->format('Y');
            $step1 = ORM::raw_execute('ALTER TABLE `data_change_logs` RENAME `data_change_logs_' . $past_year . '`');

            // Делаем новую таблицу активной
            $step2 = ORM::raw_execute('ALTER TABLE `data_change_logs_new` RENAME `data_change_logs`');
        }

        if ($step1 && $step2) {
            $subject = 'DataChangeLogsRotator выполнена успешно';
            $message = 'Успешно выполнена ротация таблицы data_change_logs';
        } else {
            $subject = 'DataChangeLogsRotator не выполнена';
            $message = 'Произошла ошибка при ротации таблицы data_change_logs';
        }

        $this->mailer->send('SIMPLE_MESSAGE', $subject, SettingsCron::ERRORS_EMAILS, [
            '[CONTENT]' => $message,
        ]);
    }
}
