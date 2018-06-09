<?php
/**
 * Отправка SMS
 *
 * @version 01.01.2018
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 */

namespace Lemurro\Api\Core\SMS;

use Lemurro\Api\App\Configs\SettingsGeneral;
use Lemurro\Api\App\Configs\SettingsSMS;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class SMS
 *
 * @package Lemurro\Api\Core\SMS
 */
class SMS
{
    /**
     * Логгер
     *
     * @var object
     */
    protected $log;

    /**
     * Конструктор
     *
     * @throws \Exception
     *
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function __construct()
    {
        $this->log = new Logger('SMS');
        $this->log->pushHandler(new StreamHandler(SettingsGeneral::FULL_ROOT_PATH . 'logs/sms.log'));
    }

    /**
     * Отправка SMS
     *
     * @param string $phone   Номер телефона получателя
     * @param string $message Сообщение
     * @param string $gateway Шлюз для передачи
     *
     * @return boolean
     *
     * @version 01.01.2018
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     */
    public function send($phone, $message, $gateway = SettingsSMS::DEFAULT_GATEWAY)
    {
        switch ($gateway) {
            case 'p1sms':
                $result = GatewayP1SMS::send($phone, $message);
                break;

            case 'smsru':
                $result = GatewaySMSRU::send($phone, $message);
                break;

            default:
                $result = [
                    'success' => false,
                    'message' => 'Указанный шлюз отсутствует.',
                ];
                break;
        }

        if ($result['success']) {
            $this->log->info($result['message']);

            return true;
        } else {
            $this->log->warning($result['message']);

            return false;
        }
    }
}
