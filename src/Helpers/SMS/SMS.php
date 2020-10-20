<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\Core\Abstracts\GatewaySMS;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use Monolog\Logger;

/**
 * @package Lemurro\Api\Core\Helpers\SMS
 */
class SMS
{
    protected array $config_sms;
    protected Logger $log;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_sms, LoggerFactory $logfactory)
    {
        $this->config_sms = $config_sms;
        $this->log = $logfactory->create('SMS');
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function send(string $phone, string $message, GatewaySMS $gateway = null): bool
    {
        if ($gateway === null) {
            $gateway = new GatewaySMSRU($this->config_sms);
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
