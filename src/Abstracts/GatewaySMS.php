<?php

/**
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\Core\Helpers\SMS\Phone;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class GatewaySMS
{
    protected array $config_sms;
    protected Phone $phone;

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function __construct(array $config_sms)
    {
        $this->config_sms = $config_sms;
        $this->phone = new Phone();
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public abstract function send(string $phone, string $message): array;
}
