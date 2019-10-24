<?php
/**
 * Валидация телефона
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 * @version 24.10.2019
 */

namespace Lemurro\Api\Core\Helpers\SMS;

use Lemurro\Api\Core\Helpers\LoggerFactory;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Monolog\Logger;

/**
 * Class Phone
 *
 * @package Lemurro\Api\Core\Helpers\SMS
 */
class Phone
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * Phone constructor.
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function __construct()
    {
        $this->log = LoggerFactory::create('SMS');
    }

    /**
     * Валидация телефона
     *
     * @param string $phone Номер телефона получателя
     *
     * @return string|null
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function validate($phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($phone, 'RU');

            if (
                $phoneUtil->isPossibleNumber($phoneNumber)
                &&
                $phoneUtil->isValidNumber($phoneNumber)
                &&
                $phoneUtil->getNumberType($phoneNumber) === PhoneNumberType::MOBILE
            ) {
                return $phoneNumber->getCountryCode() . $phoneNumber->getNationalNumber();
            }
        } catch (NumberParseException $e) {
            $this->log->error('Phone->validate(): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Это телефон?
     *
     * @param string $phone Номер телефона получателя
     *
     * @return boolean
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     * @version 24.10.2019
     */
    public function isPhone($phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($phone, 'RU');

            if (
                $phoneUtil->isPossibleNumber($phoneNumber)
                &&
                $phoneUtil->isValidNumber($phoneNumber)
                &&
                $phoneUtil->getNumberType($phoneNumber) === PhoneNumberType::MOBILE
            ) {
                return true;
            }
        } catch (NumberParseException $e) {
            return false;
        }

        return false;
    }
}
