<?php

namespace Lemurro\Api\Core\Abstracts;

/**
 * Интерфейс шлюза отправки sms
 */
interface GatewaySMS
{
    /**
     * Отправка sms
     *
     * @param string $phone   Номер телефона получателя
     * @param string $message Сообщение
     *
     * @version 18.02.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($phone, $message);
}
