<?php

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\Core\Helpers\LoggerFactory;

/**
 * Отправка SMS
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
     */
    public function __construct()
    {
        $this->log = LoggerFactory::create('SMS');
    }

    /**
     * Отправка SMS
     *
     * @param string  $phone   Номер телефона получателя
     * @param string  $message Сообщение
     * @param ?string $gateway Шлюз для передачи
     *
     * @return boolean
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
