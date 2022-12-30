<?php

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\App\Configs\SettingsSMS;
use Lemurro\Api\Core\Abstracts\GatewaySMS;

/**
 * Шлюз для отправки sms: sms.ru
 */
class GatewaySMSRU implements GatewaySMS
{
    /**
     * Отправка sms
     *
     * @param string $phone   Номер телефона получателя
     * @param string $message Сообщение
     *
     * @return array
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function send($phone, $message)
    {
        $phone_number = (new Phone())->validate($phone);

        if (empty($phone_number)) {
            return [
                'success' => false,
                'message' => 'Неверный номер телефона',
            ];
        }

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
                'message' => 'Ошибка запроса к API',
            ];
        }
    }
}
