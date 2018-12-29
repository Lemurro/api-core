<?php
/**
 * Генератор ответа
 *
 * @version 24.12.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @author  Евгений Кулагин <ekulagin59@gmail.com>
 */

namespace Lemurro\Api\Core\Helpers;

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
     * @version 24.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @author  Евгений Кулагин <ekulagin59@gmail.com>
     */
    static function data($data)
    {
        if (empty($data)) {
            return [
                'data' => [],
            ];
        } else {
            return [
                'data' => $data,
            ];
        }
    }

    /**
     * Сгенерируем одну ошибку "400 Bad Request"
     *
     * @param string $title Краткое, понятное для человека описание проблемы
     * @param array  $meta  Массив дополнительных данных об ошибке для приложения
     *
     * @return array
     *
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function error400($title, $meta = [])
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function error401($title, $meta = [])
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function error403($title, $redirect, $meta = [])
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function error404($title, $meta = [])
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function error500($title, $meta = [])
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
     * @version 29.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @author  Евгений Кулагин <ekulagin59@gmail.com>
     */
    static function error($status, $code, $title, $meta = [])
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
     * @param array $errors Массив ошибок
     *
     * @return array
     *
     * @version 24.12.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function errors($errors)
    {
        if (empty($errors)) {
            return [
                'errors' => [
                    [
                        'status' => '500 Internal Server Error',
                        'code'   => 'danger',
                        'title'  => 'Ошибка при выполнении запроса',
                    ],
                ],
            ];
        } else {
            $many_errors = [];

            foreach ($errors as $error) {
                $one_error = [
                    'status' => empty($error[0]) ? '500 Internal Server Error' : $error[0],
                    'code'   => empty($error[1]) ? 'danger' : $error[1],
                    'title'  => empty($error[2]) ? 'Ошибка при выполнении запроса' : $error[2],
                ];

                if (!empty($error[3])) {
                    $one_error['meta'] = $error[3];
                }

                $many_errors[] = $one_error;
            }

            return [
                'errors' => $many_errors,
            ];
        }
    }
}
