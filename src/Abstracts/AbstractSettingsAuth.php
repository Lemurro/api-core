<?php

/**
 * Параметры аутентификации
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsAuth
{
    /**
     * Вид аутентификации
     *   email: по электронной почте (код через email)
     *   phone: по номеру телефона (код через смс)
     *   mixed: смешанная аутентификация (в поле auth_id может быть email или номер телефона)
     */
    public static string $type = 'email';

    /**
     * Можно ли регистрировать новых пользователей (если при получении кода окажется что такого пользователя нет он будет создан)
     */
    public static bool $can_registration_users = false;

    /**
     * Количество генераций новых кодов в день
     */
    public static int $attempts_per_day = 50;

    /**
     * Время устаревания кодов аутентификации (в часах)
     */
    public static int $auth_codes_older_than = 2;

    /**
     * Время устаревания сессий (в днях), сессии которыми не пользовались
     */
    public static int $sessions_older_than = 30;

    /**
     * Привязка сессии к IP-адресу
     */
    public static bool $sessions_binding_to_ip = false;
}
