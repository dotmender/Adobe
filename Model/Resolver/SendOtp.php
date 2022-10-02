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

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Core\OtpLogin\Model\SendOtpFactory;
use Core\OtpLogin\Helper\Data as OtpHelper;
use Core\OtpLogin\Model\ValidateMobileNumber;

class SendOtp implements ResolverInterface
{
    /**
     * @var SendOtpFactory
     */
    private $sendOtpFactory;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * @var ValidateMobileNumber
     */
    protected $validateMobileNumber;

    /**
     * @param SendOtpFactory $sendOtpFactory
     * @param OtpHelper $otpHelper
     * @param ValidateMobileNumber $validateMobileNumber
     */
    public function __construct(
        SendOtpFactory $sendOtpFactory,
        OtpHelper $otpHelper,
        ValidateMobileNumber $validateMobileNumber
    ) {
        $this->sendOtpFactory = $sendOtpFactory;
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

        if (!$this->otpHelper->isOtpModuleEnabled()) {
            throw new GraphQlInputException(__('OTP Module is disabled'));
        }

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('Invalid input parameter'));
        }

        if (empty($args['input']['mobile_number'])) {
            throw new GraphQlInputException(__('Mobile number is required field.'));
        }

        $mobileNumber = $this->validateMobileNumber->validate($args['input']['mobile_number']);
        if (!$mobileNumber) {
            throw new GraphQlInputException(__('Please enter valid mobile number.'));
        }

        $event = $args['input']['event'];

        $sendOtpFactory = $this->sendOtpFactory->create();

        $isBlocked = $sendOtpFactory->checkCustomerIsBlocked($mobileNumber);
        if ($isBlocked) {
            throw new GraphQlInputException(__('Please try after some time'));
        }

        /**
         * Retrict to send consicutive OTP request
         */
        $isLimitExided = $sendOtpFactory->maxOtpRequest($mobileNumber);
        if ($isLimitExided) {
            throw new GraphQlInputException(__('Please try after some time'));
        }

        try {
            $referenceCode = $sendOtpFactory->sendOtpToMobileNumber(
                $mobileNumber,
                $event
            );
            $message['message_code'] = '200';
            $message['message'] = 'OTP Sent successfully';
            $message['otp_reference_code'] = $referenceCode;
            return $message;
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getCode()));
        }
    }
}
