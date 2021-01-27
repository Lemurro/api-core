<?php

/**
 * Шлюз для отправки sms: sms.ru
 */

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\Core\Abstracts\GatewaySMS;

class GatewaySMSRU extends GatewaySMS
{
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

        if (empty($result)) {
            return [
                'success' => false,
                'message' => 'Ошибка запроса к API',
            ];
        }

        $parsed = json_decode($result, true);

        if (!is_array($parsed)) {
            return [
                'success' => false,
                'message' => 'Непонятный ответ от API: ' . $result,
            ];
        }

        if ((string) $parsed['status'] === 'OK') {
            return [
                'success' => true,
                'message' => $result,
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка отправки: ' . $result,
        ];
    }
}
