<?php

/**
 * Параметры SMS
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsSMS
{
    /**
     * API-ключ от аккаунта в sms.ru
     */
    public static string $smsru_api_id = 'api_id';

    /**
     * Отправитель (можно оставить пустым, если не нужен)
     */
    public static string $smsru_sender = 'SenderName';
}
