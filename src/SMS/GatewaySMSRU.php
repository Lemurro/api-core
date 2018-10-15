<?php
/**
 * Шлюз для отправки sms: sms.ru
 *
 * @version 15.10.2018
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
     * @version 15.10.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($phone, $message)
    {
        $phone_number = preg_replace('/[^0-9]/', '', $phone);
        $from = (SettingsSMS::SMSRU_SENDER == '' ? '' : '&from=' . SettingsSMS::SMSRU_SENDER);
        $result = file_get_contents('https://sms.ru/sms/send?api_id=' . SettingsSMS::SMSRU_API_ID . '&to=' . $phone_number . $from . '&text=' . urlencode($message) . '&json=1');

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
