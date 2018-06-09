<?php
/**
 * Шлюз для отправки sms: sms.ru
 *
 * @version 01.01.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\App\Configs\SettingsSMS;

/**
 * Class GatewaySMSRU
 *
 * @package Lemurro\Api\Core\SMS
 */
class GatewaySMSRU
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
        $result = file_get_contents('https://sms.ru/sms/send?api_id=' . SettingsSMS::GATEWAYS['smsru']['api_id'] . '&to=' . $phone_number . '&msg=' . urlencode($message) . '&json=1');

        if ($result != '') {
            $parsed = json_decode($result, true);

            if (is_array($parsed)) {
                if ($parsed['status'] == 'OK') {
                    return [
                        'success' => true,
                        'message' => $result,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка отправки: ' . $result,
                    ];
                }
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
