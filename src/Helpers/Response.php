<?php

/**
 * Генератор ответа
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @author  Евгений Кулагин <ekulagin59@gmail.com>
 *
 * @version 16.06.2020
 */

namespace Lemurro\Api\Core\Helpers;

use Exception;
use RuntimeException;

/**
 * Class Response
 *
 * @package Lemurro\Api\Core\Helpers
 */
class Response
{
    /**
     * Сгенерируем успешный ответ
     *
     * @param array $data Массив данных
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @author  Евгений Кулагин <ekulagin59@gmail.com>
     *
     * @version 17.04.2020
     */
    public static function data($data): array
    {
        if (empty($data)) {
            return [
                'data' => [],
            ];
        }

        return [
            'data' => $data,
        ];
    }

    /**
     * Сгенерируем одну ошибку "400 Bad Request"
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function error400($title, $meta = []): array
    {
        return self::error('400 Bad Request', 'warning', $title, $meta);
    }

    /**
     * Сгенерируем одну ошибку "401 Unauthorized"
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function error401($title, $meta = []): array
    {
        return self::error('401 Unauthorized', 'warning', $title, $meta);
    }

    /**
     * Сгенерируем одну ошибку "403 Forbidden"
     *
     * @param string  $title    Краткое, понятное для человека описание проблемы
     * @param boolean $redirect Делать редирект на страницу 403 или нет
     * @param array   $meta     Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function error403($title, $redirect, $meta = []): array
    {
        $meta['redirect'] = $redirect;

        return self::error('403 Forbidden', 'warning', $title, $meta);
    }

    /**
     * Сгенерируем одну ошибку "404 Not Found"
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function error404($title, $meta = []): array
    {
        return self::error('404 Not Found', 'info', $title, $meta);
    }

    /**
     * Сгенерируем одну ошибку "500 Internal Server Error"
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function error500($title, $meta = []): array
    {
        return self::error('500 Internal Server Error', 'danger', $title, $meta);
    }

    /**
     * Сгенерируем одну ошибку
     *
     * @param string $status Код состояния HTTP или массив ошибок
     * @param string $code   Код ошибки, специфичный для приложения (danger|warning|info)
     * @param string $title  Краткое, понятное для человека описание проблемы
     * @param array  $meta   Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @author  Евгений Кулагин <ekulagin59@gmail.com>
     *
     * @version 17.04.2020
     */
    public static function error($status, $code, $title, $meta = []): array
    {
        $error = [
            'status' => empty($status) ? '500 Internal Server Error' : $status,
            'code'   => empty($code) ? 'danger' : $code,
            'title'  => empty($title) ? 'Ошибка при выполнении запроса' : $title,
        ];

        if (!empty($meta)) {
            $error['meta'] = $meta;
        }

        return [
            'errors' => [
                $error,
            ],
        ];
    }

    /**
     * Сгенерируем несколько ошибок
     *
     * @deprecated Метод будет удалён в v2.0
     *
     * @param array $errors Массив ошибок
     *
     * @return array
     *
     * @version 17.06.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public static function errors($errors): array
    {
        if (empty($errors)) {
            return self::error500('Ошибка при выполнении запроса');
        }

        $many_errors = [];

        foreach ($errors as $error) {
            if (isset($error['errors']) && is_array($error['errors']) && !empty($error['errors'])) {
                $error = $error['errors'][0];

                $one_error = [
                    'status' => empty($error['status']) ? '500 Internal Server Error' : $error['status'],
                    'code'   => empty($error['code']) ? 'danger' : $error['code'],
                    'title'  => empty($error['title']) ? 'Ошибка при выполнении запроса' : $error['title'],
                ];

                if (!empty($error['meta'])) {
                    $one_error['meta'] = $error['meta'];
                }

                $many_errors[] = $one_error;
            }
        }

        return [
            'errors' => $many_errors,
        ];
    }

    /**
     * Сгенерируем ошибку на основании Exception
     *
     * @param Exception $e Объект ошибки (используются поля: code и message)
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 17.04.2020
     */
    public static function exception($e): array
    {
        $error_method = 'error500';

        switch ($e->getCode()) {
            case 403:
                return self::error403($e->getMessage(), false);
                break;

            case 400:
            case 401:
            case 404:
                $error_method = 'error' . $e->getCode();
                break;
        }

        return self::$error_method($e->getMessage());
    }

    /**
     * Сгенерируем ошибку некорректных данных
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 27.05.2020
     */
    public static function invalidData($title = 'Некорректные входные данные', $meta = []): array
    {
        return self::error400($title, $meta);
    }

    /**
     * Превратим ошибку в RuntimeException
     *
     * @param array $errors Результат от методов error*
     *
     * @throws RuntimeException
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.06.2020
     */
    public static function errorToException($errors): array
    {
        $one_error = $errors['errors'][0];

        preg_match_all('/^\d{3}/', $one_error['status'], $matches);

        if (is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
            $code = $matches[0][0];
        } else {
            $code = 500;
        }

        throw new RuntimeException($one_error['title'], $code);
    }
}
