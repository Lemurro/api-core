<?php
/**
 * Отправка SMS
 *
 * @version 12.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\App\Configs\SettingsPath;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class SMS
 *
 * @package Lemurro\Api\Core\SMS
 */
class SMS
{
    /**
     * Логгер
     *
     * @var object
     */
    protected $log;

    /**
     * Конструктор
     *
     * @throws \Exception
     *
     * @version 12.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        $this->log = new Logger('SMS');
        $this->log->pushHandler(new StreamHandler(SettingsPath::LOGS . 'sms.log'));
    }

    /**
     * Отправка SMS
     *
     * @param string $phone   Номер телефона получателя
     * @param string $message Сообщение
     * @param string $gateway Шлюз для передачи
     *
     * @return boolean
     *
     * @version 26.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($phone, $message, $gateway = null)
    {
        if ($gateway === null) {
            $gateway = new GatewaySMSRU();
        }

        $result = $gateway->send($phone, $message);

        if ($result['success']) {
            $this->log->info($result['message']);

            return true;
        } else {
            $this->log->warning($result['message']);

            return false;
        }
    }
}
