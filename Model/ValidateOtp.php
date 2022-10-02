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

use Magento\Framework\Encryption\EncryptorInterface;
use Core\OtpLogin\Model\ResourceModel\Otp as OtpResourceModel;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Core\OtpLogin\Model\OtpFactory;
use Core\OtpLogin\Model\ReferenceCodeFactory;
use Core\OtpLogin\Model\CustomerTokenManager;
use Core\OtpLogin\Model\Config\ResponseCodes;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Core\OtpLogin\Helper\Data as OtpHelper;

class ValidateOtp
{
    /**
     * @var \Core\OtpLogin\Model\OtpFactory
     */
    private $otpFactory;

    /**
     * @var \Core\OtpLogin\Model\ReferenceCodeFactory
     */
    private $referenceCodeFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var OtpResourceModel
     */
    protected $otpResourceModel;

    /**
     * @var \Core\OtpLogin\Model\CustomerTokenManager
     */
    protected $customerTokenManager;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * Construct method
     *
     * @param \Core\OtpLogin\Model\OtpFactory $otpFactory
     * @param OtpHelper $otpHelper
     * @param \Core\OtpLogin\Model\ReferenceCodeFactory $referenceCodeFactory
     * @param EncryptorInterface $encryptor
     * @param OtpResourceModel $otpResourceModel
     * @param \Core\OtpLogin\Model\CustomerTokenManager $customerTokenManager
     * @param TimezoneInterface $date
     */
    public function __construct(
        OtpFactory $otpFactory,
        OtpHelper $otpHelper,
        ReferenceCodeFactory $referenceCodeFactory,
        EncryptorInterface $encryptor,
        OtpResourceModel $otpResourceModel,
        CustomerTokenManager $customerTokenManager,
        TimezoneInterface $date
    ) {
        $this->otpFactory = $otpFactory;
        $this->otpHelper = $otpHelper;
        $this->referenceCodeFactory = $referenceCodeFactory;
        $this->encryptor = $encryptor;
        $this->otpResourceModel = $otpResourceModel;
        $this->customerTokenManager = $customerTokenManager;
        $this->date = $date;
    }

    /**
     * OTP Validation
     *
     * @param String $mobileNumber
     * @param String $mobileOtp
     * @param String $refereceCode
     * @return int
     * @throws \Exception
     */
    public function validateMobileOtp($mobileNumber, $mobileOtp, $refereceCode)
    {
        $systemOtp = '';
        $decryptedSystemOtp = '';
        $otpCollection = $this->otpFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('mobile_number', $mobileNumber)
            ->setOrder('otp_id', 'DESC')
            ->setPageSize(1)->setCurPage(1);

        if ($otpCollection->getSize() > 0) {
            foreach ($otpCollection as $val) {
                $systemOtp = $val->getOtp();
                $otpId = $val->getOtpId();
                $otpCreateDate = $val->getCreatedAt();
            }

            //Check OTP validation time has exedeed
            $isOtpVerifiedLimitOver = $this->isOtpVerifyLimitOver($otpCollection);
            if ($isOtpVerifiedLimitOver) {
                return ResponseCodes::OTP_LIMIT_REACHED;
            }

            //Check OTP is expired
            $isOtpExpired = $this->isOtpExpired($otpCreateDate);
            if ($isOtpExpired) {
                $this->updateVerificationRetry($otpCollection);
                return ResponseCodes::OTP_EXPIRED;
            }

            //Validate reference code against the OTP
            $isRfcValid = $this->validateRefereceCode($otpId, $refereceCode);
            if (!$isRfcValid) {
                $this->updateVerificationRetry($otpCollection);
                return ResponseCodes::RF_CODE_INVALID;
            }

            //Successfully Validate OTP
            $decryptedSystemOtp = $this->decrypt($systemOtp);
            if ($decryptedSystemOtp == $mobileOtp) {
                $this->makeOtpInactive($otpCollection);
                return ResponseCodes::OTP_VARIFIED;
            }
        }
        $this->updateVerificationRetry($otpCollection);
        return ResponseCodes::OTP_INVALID;
    }

    /**
     * Generate Customer Login Token
     *
     * @param String $mobileNumber
     * @param object $store
     * @return false|string
     */
    public function generateCustomerToken($mobileNumber, $store)
    {
        $isCustomerExist = $this->otpResourceModel->checkMobileNumberAvailable($mobileNumber, $store);
        if (empty($isCustomerExist)) {
            return false;
        }
        $customerEntiryId = $isCustomerExist['entity_id'];
        return $this->customerTokenManager->createCustomerTokenById($customerEntiryId);
    }

    /**
     * Update OTP Flags
     *
     * @param Object $otpCollection
     * @return int|mixed|void
     * @throws \Exception
     */
    public function makeOtpInactive($otpCollection)
    {
        try {
            $otpVerifyData = [
                'is_verified' => true,
                'retry_attempts' => 0
            ];
            foreach ($otpCollection as $col) {
                $col->setIsActive(0);
                $col->setStatus(0);
                $col->setVerifyOtpData(json_encode($otpVerifyData));
            }
            $otpCollection->save();
        } catch (Exception $e) {
            return $e->getCode();
        }
    }

    /**
     * Update OTP Verify Attempts Data
     *
     * @param object $otpCollection
     * @return int|mixed|void
     */
    public function updateVerificationRetry($otpCollection)
    {
        try {
            foreach ($otpCollection as $col) {
                $otpVerifyData = json_decode($col->getVerifyOtpData(), true);
                if (array_key_exists('retry_attempts', $otpVerifyData)) {
                    $otpVerifyData['retry_attempts'] = $otpVerifyData['retry_attempts']+1;
                }
                //Updating the retry attempts
                $col->setVerifyOtpData(json_encode($otpVerifyData));
            }
            $otpCollection->save();
        } catch (Exception $e) {
            return $e->getCode();
        }
    }

    /**
     * Validate Reference Code
     *
     * @param String $otpId
     * @param String $refereceCode
     * @return bool
     */
    public function validateRefereceCode($otpId, $refereceCode)
    {
        $rfcCollection = $this->referenceCodeFactory->create()
            ->getCollection()->addFieldToFilter('otp_id', $otpId);
        foreach ($rfcCollection as $val) {
            $systemRf = $val->getReferenceCode();
            if ($systemRf == $refereceCode) {
                return true;
            }
        }
        return false;
    }

    /**
     * Decrypt Encrypted OTP
     *
     * @param  string $systemOtp
     * @return string
     */
    public function decrypt($systemOtp)
    {
        return $this->encryptor->decrypt($systemOtp);
    }

    /**
     * Check If OTP Expire
     *
     * @param Date $otpCreateDate
     * @return bool
     */
    public function isOtpExpired($otpCreateDate)
    {
        $otpExpiredTime = $this->otpHelper->getExpiredTime();
        $currentDate = strtotime($this->date->date()->format('Y-m-d H:i:s'));
        $updateData = strtotime($this->date->date($otpCreateDate)->format('Y-m-d H:i:s'));
        $interval = $currentDate-$updateData;
        if (round($interval/60, 2) > $otpExpiredTime) {
            return true;
        }
        return false;
    }

    /**
     * Check OTP Verify Limit
     *
     * @param object $otpCollection
     * @return bool
     */
    public function isOtpVerifyLimitOver($otpCollection)
    {
        $maxRetryAllow = $this->otpHelper->getMaxTry();
        foreach ($otpCollection as $col) {
            $otpVerifyData = json_decode($col->getVerifyOtpData(), true);
            if (array_key_exists('retry_attempts', $otpVerifyData) &&
                $otpVerifyData['retry_attempts'] >= $maxRetryAllow) {
                return true;
            }
        }
        return false;
    }
}
