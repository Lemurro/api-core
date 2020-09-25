<?php

/**
 * Параметры обслуживания
 *
 * При включении блокирует все запросы и возвращает ошибку с текстом из константы "MESSAGE"
 * Не блокирует запросы пользователей с правами "Администратор"
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.09.2020
 */

namespace Lemurro\Api\Core\Abstracts;

/**
 * @package Lemurro\Api\Core\Abstracts
 */
abstract class AbstractSettingsMaintenance
{
    /**
     * Включить \ Выключить обслуживание проекта
     */
    public static bool $active = false;

    /**
     * Сообщение об обслуживании
     */
    public static string $message = 'Проект "Lemurro" временно остановлен для обслуживания, пожалуйста повторите через 5 минут или обновите страницу';
}
