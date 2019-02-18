<?php
/**
 * Шлюз для отправки sms: sms.ru
 *
 * @version 18.02.2019
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\App\Configs\SettingsSMS;
use Lemurro\Api\Core\Helpers\LoggerFactory;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

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
     * @version 18.02.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($phone, $message)
    {
        $phone_number = $this->validatePhone($phone);

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

    /**
     * Валидация телефона
     *
     * @param string $phone Номер телефона получателя
     *
     * @return string|null
     *
     * @version 18.02.2019
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    protected function validatePhone($phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($phone, 'RU');

            if ($phoneUtil->isPossibleNumber($phoneNumber) && $phoneUtil->isValidNumber($phoneNumber) && $phoneUtil->getNumberType($phoneNumber) === 'MOBILE') {
                return $phoneNumber->getCountryCode() . $phoneNumber->getNationalNumber();
            }
        } catch (NumberParseException $e) {
            $log = LoggerFactory::create('SMS');
            $log->error('GatewaySMSRU->validatePhone(): ' . $e->getMessage());
        }

        return null;
    }
}
