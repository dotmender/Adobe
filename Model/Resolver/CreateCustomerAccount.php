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
use Core\OtpLogin\Model\CustomerTokenManager;
use Core\OtpLogin\Model\ResourceModel\Otp as OtpResourceModel;
use Core\OtpLogin\Helper\Data as OtpHelper;

class CreateCustomerAccount implements ResolverInterface
{
    /**
     * @var SendOtpFactory
     */
    private $customerTokenManager;

    /**
     * @var OtpResourceModel
     */
    protected $otpResourceModel;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * @param CustomerTokenManager $customerTokenManager
     * @param OtpResourceModel $otpResourceModel
     * @param OtpHelper $otpHelper
     */
    public function __construct(
        CustomerTokenManager $customerTokenManager,
        OtpResourceModel $otpResourceModel,
        OtpHelper $otpHelper
    ) {
        $this->customerTokenManager = $customerTokenManager;
        $this->otpResourceModel = $otpResourceModel;
        $this->otpHelper = $otpHelper;
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
        if (!$this->otpHelper->isOtpModuleEnabled()) {
            throw new GraphQlInputException(__('OTP Module is disabled'));
        }

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('Invalid input parameter'));
        }

        $store = $context->getExtensionAttributes()->getStore();
        $mobileNumber = $args['input']['mobile_number'];
        $fullname = $args['input']['fullname'];
        $browserData = $args['input']['browser_data'];

        $token = $this->customerTokenManager->createCustomerAndGetToken(
            $fullname,
            $mobileNumber,
            $store
        );

        if (!$token) {
            throw new GraphQlInputException(__('Something went wrong'));
        }

        $output['token'] = $token;
        $output['message_code'] = '200';
        $output['message'] = 'Customer registered successfully';
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
}
