<?php
/*******************************************************************************
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2022 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Adobe permits you to use and modify this file
 * in accordance with the terms of the Adobe license agreement
 * accompanying it (see LICENSE_ADOBE_PS.txt).
 * If you have received this file from a source other than Adobe,
 * then your use, modification, or distribution of it
 * requires the prior written permission from Adobe.
 ******************************************************************************/

namespace Core\OtpLogin\Model;

class ValidateMobileNumber
{
    public const MOBILE_REG_EXPRESSION = '/[\s_-]+/';

    /**
     * Clean Mobile Number
     *
     * @param String $mobileNumber
     * @return false|int
     */
    public function formatMobile($mobileNumber)
    {
        //Remvoe Whitespace, Dash & Underscore from String
        return preg_replace(self::MOBILE_REG_EXPRESSION, '', $mobileNumber);
    }

    /**
     * Validate Mobile Number with Digit
     *
     * @param String $mobileNumber
     * @return false|int
     */
    public function validate($mobileNumber)
    {
        $cleanMobileNumber = $this->formatMobile($mobileNumber);
        if (preg_match('/^[0-9]{10}+$/', $cleanMobileNumber)) {
            return $cleanMobileNumber;
        }
        return false;
    }
}
