<?php
/**
 * Проверка валидности сессии
 *
 * @version 26.07.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core;

use Carbon\Carbon;
use Lemurro\Api\App\Configs\SettingsGeneral;

/**
 * Class Session
 *
 * @package Lemurro\Api\Core
 */
class Session
{
    /**
     * Проверим сессию на валидность
     *
     * @param string $session_id ИД сессии
     *
     * @return array
     *
     * @version 26.07.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function check($session_id)
    {
        $now = Carbon::now(SettingsGeneral::TIMEZONE);
        $checked_at = $now->toDateTimeString();

        \ORM::for_table('sessions')
            ->where_lt('checked_at', $now->subDays(SettingsGeneral::SESSIONS_OLDER_THAN))
            ->delete_many();

        $session = \ORM::for_table('sessions')
            ->where_equal('session', $session_id)
            ->find_one();
        if (is_object($session)) {
            if (SettingsGeneral::SESSIONS_BINDING_TO_IP && $session->ip !== $_SERVER['REMOTE_ADDR']) {
                $session->delete();

                return [
                    'errors' => [
                        [
                            'status' => '401 Unauthorized',
                            'code'   => 'info',
                            'title'  => 'Необходимо авторизоваться.',
                        ],
                    ],
                ];
            }

            $session->checked_at = $checked_at;
            $session->save();

            return $session->as_array();
        } else {
            return [
                'errors' => [
                    [
                        'status' => '401 Unauthorized',
                        'code'   => 'info',
                        'title'  => 'Необходимо авторизоваться.',
                    ],
                ],
            ];
        }
    }
}
