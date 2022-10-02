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

declare(strict_types=1);

namespace Core\OtpLogin\Model\Resolver;

use Core\OtpLogin\Model\Config\ResponseCodes;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Core\OtpLogin\Model\ValidateOtpFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Core\OtpLogin\Helper\Data as OtpHelper;
use Core\OtpLogin\Model\ValidateMobileNumber;

class ValidateOtp implements ResolverInterface
{
    /**
     * @var ValidateOtpFactory
     */
    protected $validateOtpFactory;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * @var ValidateMobileNumber
     */
    protected $validateMobileNumber;

    /**
     * @param ValidateOtpFactory $validateOtpFactory
     * @param OtpHelper $otpHelper
     * @param ValidateMobileNumber $validateMobileNumber
     */
    public function __construct(
        ValidateOtpFactory $validateOtpFactory,
        OtpHelper $otpHelper,
        ValidateMobileNumber $validateMobileNumber
    ) {
        $this->validateOtpFactory = $validateOtpFactory;
        $this->otpHelper = $otpHelper;
        $this->validateMobileNumber = $validateMobileNumber;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $output = [];

        //Check Module is enable
        $this->isModuleEnable();

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('Invalid input parameter'));
        }

        $mobileNumber = $this->validateMobileNumber->validate($args['input']['mobile_number']);
        if (!$mobileNumber) {
            throw new GraphQlInputException(__('Please enter valid mobile number.'));
        }

        $mobileOtp = $args['input']['mobile_otp'];
        $refereceCode = $args['input']['otp_reference_code'];

        $validateOtp = $this->validateOtpFactory->create();
        $validateOtpResponseCode = $validateOtp->validateMobileOtp(
            $mobileNumber,
            $mobileOtp,
            $refereceCode
        );

        $retryAfter = $this->otpHelper->getMaxTry();
        if ($validateOtpResponseCode == ResponseCodes::OTP_LIMIT_REACHED) {
            throw new GraphQlInputException(__('Wait for '.$retryAfter.' minutes before trying again.'));
        }

        if ($validateOtpResponseCode == ResponseCodes::OTP_INVALID ||
            $validateOtpResponseCode == ResponseCodes::RF_CODE_INVALID
        ) {
            throw new GraphQlInputException(__('OTP entered is wrong'));
        }

        if ($validateOtpResponseCode == ResponseCodes::OTP_EXPIRED) {
            throw new GraphQlInputException(__('OTP is expired'));
        }

        $store = $context->getExtensionAttributes()->getStore();
        $isMobileExist = $validateOtp->generateCustomerToken($mobileNumber, $store);
        if (!$isMobileExist) {
            $output['message_code'] = '200';
            $output['message'] = 'OTP have been verified successfully & Customer is not available move to sign-up page';
            return $output;
        }

        $output['token'] = $isMobileExist;
        $output['message_code'] = '200';
        $output['message'] = 'OTP have been verified successfully';
        $cookiesData = [
            "store_id" => 1,
            "customer_id" => "12345",
            "customer_group" => "3",
            "customer_token" => "faghfd",
            "firstname" => "Rajesh",
            "lastname" => "Rathod"
        ];
        $output['cookies_data'] = $cookiesData;
        return $output;
    }

    /**
     * Is Module Enable
     *
     * @return void
     * @throws GraphQlInputException
     */
    public function isModuleEnable()
    {
        if (!$this->otpHelper->isOtpModuleEnabled()) {
            throw new GraphQlInputException(__('OTP Module is disabled'));
        }
    }
}
