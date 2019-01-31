<?php
/**
 * Отправка SMS
 *
 * @version 31.01.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\Core\Helpers\LoggerFactory;

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
     * @version 31.01.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        $this->log = LoggerFactory::create('SMS');
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
