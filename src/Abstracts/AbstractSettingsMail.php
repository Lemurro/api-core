<?php

/**
 * Параметры email
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsMail
{
    // ОСНОВНОЙ КАНАЛ

    /**
     * Почтовый адрес
     */
    public static string $app_email = 'no-reply@domain.tld';

    /**
     * Отправка через SMTP с авторизацией
     */
    public static bool $smtp = true;

    /**
     * Тип протокола (ssl|tls)
     */
    public static string $smtp_security = 'ssl';

    /**
     * Сервер
     */
    public static string $smtp_host = 'HOST';

    /**
     * Порт
     */
    public static int $smtp_port = 0;

    /**
     * Адрес почты
     */
    public static string $smtp_username = 'no-reply@domain.tld';

    /**
     * Пароль от почтового ящика
     */
    public static string $smtp_password = 'PASSWORD';

    // РЕЗЕРВНЫЙ КАНАЛ (ВСЕГДА SMTP)

    /**
     * Включить (true) или выключить (false) отправку через резервный канал, в случае сбоя отправки через основной
     */
    public static bool $reserve = false;

    /**
     * Почтовый адрес
     */
    public static string $reserve_app_email = 'no-reply@domain.tld';

    /**
     * Тип протокола (ssl|tls)
     */
    public static string $reserve_smtp_security = 'ssl';

    /**
     * Сервер
     */
    public static string $reserve_smtp_host = 'HOST';

    /**
     * Порт
     */
    public static int $reserve_smtp_port = 0;

    /**
     * Адрес почты
     */
    public static string $reserve_smtp_username = 'no-reply@domain.tld';

    /**
     * Пароль от почтового ящика
     */
    public static string $reserve_smtp_password = 'PASSWORD';
}
