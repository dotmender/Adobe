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

use Core\OtpLogin\Api\SendOtpInterface;
use Core\OtpLogin\Model\OtpFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Core\OtpLogin\Model\ResourceModel\Otp as OtpResourceModel;
use Core\OtpLogin\Model\ReferenceCodeFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Core\OtpLogin\Helper\Data as OtpHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SendOtp
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Core\OtpLogin\Model\OtpFactory
     */
    protected $otp;

    /**
     * @var OtpResourceModel
     */
    protected $otpResourceModel;

    /**
     * @var \Core\OtpLogin\Model\ReferenceCodeFactory
     */
    protected $referenceCode;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @param EncryptorInterface $encryptor
     * @param \Core\OtpLogin\Model\OtpFactory $otp
     * @param OtpResourceModel $otpResourceModel
     * @param \Core\OtpLogin\Model\ReferenceCodeFactory $referenceCode
     * @param OtpHelper $otpHelper
     * @param TimezoneInterface $date
     */
    public function __construct(
        EncryptorInterface $encryptor,
        OtpFactory $otp,
        OtpResourceModel $otpResourceModel,
        ReferenceCodeFactory $referenceCode,
        OtpHelper $otpHelper,
        TimezoneInterface $date
    ) {
        $this->encryptor = $encryptor;
        $this->otpResourceModel = $otpResourceModel;
        $this->otp = $otp;
        $this->referenceCode = $referenceCode;
        $this->otpHelper = $otpHelper;
        $this->date = $date;
    }

    /**
     * Send OTP To Mobile Number
     *
     * @param  String $mobileNumber
     * @param  $string $event
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendOtpToMobileNumber($mobileNumber, $event)
    {
        $mobileOtp = $this->otp->create()->generateOtp();
        $encryptedOtp = $this->encrypt((string)$mobileOtp);
        $this->sendOtpToCustomer($mobileOtp);
        $refereceCode = $this->saveEncryptedOtp($encryptedOtp, $mobileNumber, $event);
        return $refereceCode;
    }

    /**
     * Encrypt OTP
     *
     * @param  string $otp
     * @return string
     */
    public function encrypt($otp)
    {
        $encrypt =  $this->encryptor->encrypt($otp);
        return $encrypt;
    }

    /**
     * Send OTP To Customer API Call
     *
     * @param String $mobileOtp
     * @return bool
     */
    public function sendOtpToCustomer($mobileOtp)
    {
        return true;
    }

    /**
     * Save OTP In DB
     *
     * @param String $mobileOtp
     * @param String $mobileNumber
     * @param String $event
     * @return string
     */
    public function saveEncryptedOtp($mobileOtp, $mobileNumber, $event)
    {
        try {
            $otpModel = $this->otp->create();
            $otpVerifyData = [
                'is_verified' => false,
                'retry_attempts' => 0
            ];
            $data = [
                'otp' => $mobileOtp,
                'mobile_number' => $mobileNumber,
                'is_active' => 1,
                'otp_event' => $event,
                'status' => 1,
                'verify_otp_data' => json_encode($otpVerifyData)
            ];

            /**
             * Save Encrypted mobile OTP
             */
            $otpModel->setData($data)->save();

            /**
             * Save OTP Reference Code
             */
            $refernceCode = $this->referenceCode->create()->generateReferenceCode();
            $this->saveReferenceCode($refernceCode, $mobileOtp);
            return $refernceCode;
        } catch (\Exception $e) {
            return $e->getCode();
        }
    }

    /**
     * Save Reference Code in BD
     *
     * @param String $refernceCode
     * @param String $mobileOtp
     * @return void
     */
    public function saveReferenceCode($refernceCode, $mobileOtp)
    {
        $referenceCodeModel = $this->referenceCode->create();
        $otpEntityId = $this->otpResourceModel->getOtpIdByOtp($mobileOtp);
        $data = [
            'reference_code' => $refernceCode,
            'otp_id' => $otpEntityId,
            'is_active' => 1
        ];
        $referenceCodeModel->setData($data)->save();
    }

    /**
     * Check Mobile Available in DB
     *
     * @param String $mobileNumber
     * @return bool
     */
    public function isMobileNumberAvailable($mobileNumber)
    {
        $isAvailable = $this->otpResourceModel->checkMobileNumberAvailable($mobileNumber);
        if (count($isAvailable) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Maximum OTP Request
     *
     * @param String $mobileNumber
     * @return bool
     */
    public function maxOtpRequest($mobileNumber)
    {
        $interval = $this->otpHelper->getOtpCalculationTimeInterval();
        $maxRequest = $this->otpHelper->getMaxTry();
        $data = $this->otpResourceModel->sendOtpLimit($mobileNumber, $interval);
        if (count($data) >= $maxRequest) {
            $otpId = $data[0]['otp_id'];
            $this->otpResourceModel->blockCustomerToSendOtp($otpId);
            return true;
        }
        return false;
    }

    /**
     * Check Customer Is Blocked To Send OTP
     *
     * @param String $mobileNumber
     * @return bool
     */
    public function checkCustomerIsBlocked($mobileNumber)
    {
        $otpRecord = $this->otpResourceModel->checkCustomerIsBlocked($mobileNumber);
        $lockPeriod = $this->otpHelper->getLockingPeriod();

        if (!empty($otpRecord)) {
            $currentTime = strtotime($this->date->date()->format('Y-m-d H:i:s'));
            $bockedTime = strtotime($this->date->date($otpRecord['created_at'])->format('Y-m-d H:i:s'));
            $interval = $currentTime-$bockedTime;
            if (round($interval/60, 2) < $lockPeriod && $otpRecord['is_blocked']) {
                return true;
            }
        }
        return false;
    }
}
