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
namespace Core\OtpLogin\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public const XML_TWILIOSMS_ACCOUNTSID = 'AC24edc4d65998f73ad89e73d099ec9b73';
    public const XML_TWILIOSMS_AUTHTOKEN = '60accfaff12c9bf52591c29912b3f1c8';
    public const XML_TWILIOSMS_MOBILENUMBER = '';
    public const XML_OTPLOGIN_OTPENABLED = 'otp_login/general/enabled';
    public const XML_OTPLOGIN_MAXTRY = 'otp_login/otp_config/max_try';
    public const XML_OTPLOGIN_OTPEXPIREDTIME = 'otp_login/otp_config/otp_expired_time';
    public const XML_OTPLOGIN_OTPVALIDATIONTIME = 'otp_login/otp_config/otp_validation_time';
    public const XML_OTPLOGIN_OTPLOCKINGPERIOD = 'otp_login/otp_config/otp_locking_period';
    public const XML_OTPLOGIN_OTPINTERVALREMINDER = 'otp_login/otp_config/interval_reminder';
    public const XML_OTPLOGIN_OTPRESENDOTP = 'otp_login/otp_config/resend_otp';
    public const XML_OTPLOGIN_OTPFORMAT = 'otp_login/otp_config/otp_format';
    public const XML_OTPLOGIN_OTPLENGTH = 'otp_login/otp_config/otp_length';
    public const XML_OTPLOGIN_OTPCLIENTID = 'otp_login/api/client_id';
    public const XML_OTPLOGIN_OTPSECRETKEY = 'otp_login/api/secret_key';
    public const XML_OTPLOGIN_OTPAPPTOKEN = 'otp_login/api/app_token';
    public const XML_OTPLOGIN_OTPENDPOINT = 'otp_login/api/gateway_production';
    public const XML_OTPLOGIN_OTPENDPOINTSANDBOX = 'otp_login/api/gateway_sandbox';
    public const XML_OTPLOGIN_OTPENVIRONMENT = 'otp_login/api/env_mode';
    public const XML_OTPLOGIN_OTPLOG = 'otp_login/otp_config/otp_log';
    public const XML_OTP_GENERATE_INTERVAL = 'otp_login/otp_config/generate_otp_interval';
    public const XML_TEST_OTP = 'otp_login/otp_config/test_otp';
    public const XML_TEST_REFERENCE_CODE = 'otp_login/otp_config/test_reference_code';

    /**
     * To get the Twilio account id
     *
     * @return string
     */
    public function getTwilioSid()
    {
        return self::XML_TWILIOSMS_ACCOUNTSID;
    }

    /**
     * To get the Twilio token
     *
     * @return string
     */
    public function getTwilioToken()
    {
        return self::XML_TWILIOSMS_AUTHTOKEN;
    }

    /**
     * To get the Twilio mobile number
     *
     * @return string
     */
    public function getTwilioMobileNumber()
    {
        return self::XML_TWILIOSMS_MOBILENUMBER;
    }

    /**
     * Get Is OTP Module Enable
     *
     * @return bool
     */
    public function isOtpModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_OTPLOGIN_OTPENABLED
        );
    }

    /**
     * Get Maximum Retry Attempts for otp
     *
     * @return int
     */
    public function getMaxTry()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_MAXTRY);
    }

    /**
     * Get expiration time for otp
     *
     * @return int
     */
    public function getExpiredTime()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPEXPIREDTIME);
    }

    /**
     * Get validation time for otp
     *
     * @return int
     */
    public function getValidationTime()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPVALIDATIONTIME);
    }

    /**
     * Get locking time for otp
     *
     * @return int
     */
    public function getLockingPeriod()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPLOCKINGPERIOD);
    }

    /**
     * Get interval remainder time for otp
     *
     * @return int
     */
    public function getIntervalRemainder()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPINTERVALREMINDER);
    }

    /**
     * Get interval remainder time for resending otp
     *
     * @return int
     */
    public function getResendOtp()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPRESENDOTP);
    }

    /**
     * Get Otp format for otp
     *
     * @return int
     */
    public function getOtpFormat()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPFORMAT);
    }

    /**
     * Get otp length for otp
     *
     * @return int
     */
    public function getOtpLength()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPLENGTH);
    }

    /**
     * Get Client Id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPCLIENTID);
    }

    /**
     * Get Secret Key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPSECRETKEY);
    }

    /**
     * Get App Token
     *
     * @return string
     */
    public function getAppToken()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPAPPTOKEN);
    }

    /**
     * Returns environment mode
     *
     * @return string
     */
    public function getEnvironmentMode()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPENVIRONMENT);
    }

    /**
     * Get End point url for production
     *
     * @return string
     */
    public function getProductionUrl()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPENDPOINT);
    }

    /**
     * Get End point url for sandbox
     *
     * @return string
     */
    public function getSandboxUrl()
    {
        return $this->scopeConfig->getValue(self::XML_OTPLOGIN_OTPENDPOINTSANDBOX);
    }

    /**
     * Get Log is enable/disable for otp
     *
     * @return bool
     */
    public function isOtpDebugLogEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_OTPLOGIN_OTPLOG);
    }

    /**
     * Get OTP Genration Interval Time
     *
     * @return string
     */
    public function getOtpCalculationTimeInterval()
    {
        return $this->scopeConfig->getValue(self::XML_OTP_GENERATE_INTERVAL);
    }

    /**
     * Get Test OTP
     *
     * @return string
     */
    public function getTestOTP()
    {
        return $this->scopeConfig->getValue(self::XML_TEST_OTP);
    }

    /**
     * Get Test Reference Code
     *
     * @return string
     */
    public function getTestReferenceCode()
    {
        return $this->scopeConfig->getValue(self::XML_TEST_REFERENCE_CODE);
    }
}
