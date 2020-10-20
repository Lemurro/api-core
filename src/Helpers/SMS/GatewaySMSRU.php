<?php

/**
 * Шлюз для отправки sms: sms.ru
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 14.10.2020
 */

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\Core\Abstracts\GatewaySMS;

/**
 * @package Lemurro\Api\Core\Helpers\SMS
 */
class GatewaySMSRU extends GatewaySMS
{
    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 14.10.2020
     */
    public function send(string $phone, string $message): array
    {
        $phone_number = $this->phone->normalize($phone);
        if (empty($phone_number)) {
            return [
                'success' => false,
                'message' => 'Неверный номер телефона',
            ];
        }

        $from = (empty($this->config_sms['smsru_sender']) ? '' : '&from=' . $this->config_sms['smsru_sender']);
        $result = file_get_contents('https://sms.ru/sms/send?api_id=' . $this->config_sms['smsru_api_id'] . '&to=' . $phone_number . $from . '&text=' . urlencode($message) . '&json=1');

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
