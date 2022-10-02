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

use Braintree\Exception;
use Core\OtpLogin\Model\OtpFactory;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\Math\Random;
use Magento\Customer\Model\CustomerFactory;

class CustomerTokenManager
{
    /**
     * @var TokenFactory
     */
    protected $tokenModelFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Random
     */
    protected $_mathRandom;

    /**
     * @var \Core\OtpLogin\Model\OtpFactory
     */
    private $otpFactory;

    protected const DUMMY_EMAIL_PREFIX = 'otplogin_';
    protected const EMAIL_STRING_LENGTH = 8;
    protected const EMAIL_DOMAIN = '@xyz.com';

    /**
     * Construct Method
     *
     * @param TokenFactory $tokenModelFactory
     * @param CustomerFactory $customerFactory
     * @param Random $mathRandom
     * @param \Core\OtpLogin\Model\OtpFactory $otpFactory
     */
    public function __construct(
        TokenFactory $tokenModelFactory,
        CustomerFactory $customerFactory,
        Random $mathRandom,
        OtpFactory $otpFactory
    ) {
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->customerFactory  = $customerFactory;
        $this->_mathRandom = $mathRandom;
        $this->otpFactory = $otpFactory;
    }

    /**
     * Generate Customer Token
     *
     * @param String $customerId
     * @return string
     */
    public function createCustomerTokenById($customerId)
    {
        return $this->_tokenModelFactory->create()
            ->createCustomerToken($customerId)->getToken();
    }

    /**
     * Create Customer
     *
     * @param String $fullname
     * @param String $mobileNumber
     * @param Object $store
     * @return false|string
     * @throws \Exception
     */
    public function createCustomerAndGetToken($fullname, $mobileNumber, $store)
    {
        try {
            $websiteId  = $store->getWebsiteId();
            $storeId = $store->getId();
            $customer   = $this->customerFactory->create();
            $email = $this->generateDummyEmail($fullname);
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($storeId);
            $customer->setEmail($email);
            $customer->setFirstname($fullname);
            $customer->setLastname($fullname);
            $customer->setPhone($mobileNumber);
            $customer->save();

            $this->updateRegistrationFlag($mobileNumber);

            return $this->createCustomerTokenById($customer->getId());
        } catch (Exception $e) {
            $e->getCode();
        }
        return false;
    }

    /**
     * Generate Dummy Email Address
     *
     * @param String $fullname
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateDummyEmail($fullname)
    {

        return self::DUMMY_EMAIL_PREFIX.
               $this->_mathRandom->getRandomString(self::EMAIL_STRING_LENGTH).'_'.
               str_replace(' ', '_', $fullname).
               self::EMAIL_DOMAIN;
    }

    /**
     * Update Customer Registration Flag
     *
     * @param String $mobileNumber
     * @return int|mixed|void
     */
    public function updateRegistrationFlag($mobileNumber)
    {
        $otpCollection = $this->otpFactory->create()->getCollection()
            ->addFieldToFilter('mobile_number', $mobileNumber)
            ->setOrder('otp_id', 'desc')
            ->setPageSize(1)->setCurPage(1);

        if ($otpCollection->getSize() > 0) {
            try {
                foreach ($otpCollection as $col) {
                    $col->setOtpEvent('registration');
                    $col->setIsRegistered(1);
                }
                $otpCollection->save();
            } catch (Exception $e) {
                return $e->getCode();
            }
        }
    }
}
