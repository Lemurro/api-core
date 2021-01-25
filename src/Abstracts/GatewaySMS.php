<?php

namespace Lemurro\Api\Core\Abstracts;

use Lemurro\Api\Core\Helpers\SMS\Phone;

abstract class GatewaySMS
{
    protected array $config_sms;
    protected Phone $phone;

    public function __construct(array $config_sms)
    {
        $this->config_sms = $config_sms;
        $this->phone = new Phone();
    }

    abstract public function send(string $phone, string $message): array;
}
