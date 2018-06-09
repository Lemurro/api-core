<?php
/**
 * Шлюз для отправки sms: p1sms.ru
 *
 * @version 01.01.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\App\Configs\SettingsSMS;

/**
 * Class GatewayP1SMS
 *
 * @package Lemurro\Api\Core\SMS
 */
class GatewayP1SMS
{
    /**
     * Отправка sms
     *
     * @param string $phone   Номер телефона получателя
     * @param string $message Сообщение
     *
     * @return array
     *
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    static function send($phone, $message)
    {
        $phone_number = preg_replace('/[^0-9]/', '', $phone);
        $result = file_get_contents('http://95.213.129.83/sendsms.php?user=' . SettingsSMS::GATEWAYS['p1sms']['user'] . '&pwd=' . SettingsSMS::GATEWAYS['p1sms']['password'] . '&sadr=' . SettingsSMS::GATEWAYS['p1sms']['sender'] . '&dadr=' . $phone_number . '&text=' . urlencode($message));

        if ($result != '') {
            if (preg_match('/^\d+$/', $result)) {
                return [
                    'success' => true,
                    'message' => $result,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Непонятный ответ от API: ' . $result,
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка запроса к API.',
            ];
        }
    }
}
