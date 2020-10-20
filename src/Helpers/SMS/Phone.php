<?php

/**
 * Валидация телефона
 *
 * @author  Дмитрий Щербаков <atomcms@ya.ru>
 *
 * @version 16.10.2020
 */

namespace Lemurro\Api\Core\Helpers\SMS;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * @package Lemurro\Api\Core\Helpers\SMS
 */
class Phone
{
    /**
     * @return string|null
     *
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    public function normalize(string $phone, string $region = 'RU')
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($phone, $region);

        try {
            if (
                $phoneUtil->isPossibleNumber($phoneNumber)
                &&
                $phoneUtil->isValidNumber($phoneNumber)
                &&
                $phoneUtil->getNumberType($phoneNumber) === PhoneNumberType::MOBILE
            ) {
                $country_code = (string) $phoneNumber->getCountryCode();
                $national_number = (string) $phoneNumber->getNationalNumber();

                return $country_code . $national_number;
            }
        } catch (NumberParseException $e) {
            return null;
        }

        return null;
    }

    /**
     * @author  Дмитрий Щербаков <atomcms@ya.ru>
     *
     * @version 16.10.2020
     */
    public function hasPhone(string $phone, string $region = 'RU'): bool
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($phone, $region);

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
