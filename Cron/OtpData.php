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

namespace Core\OtpLogin\Cron;

use Core\OtpLogin\Model\ResourceModel\Otp\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as customerFactory;
use Magento\Framework\Serialize\Serializer\Json;

class OtpData
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var customerFactory
     */
    private $customerFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param customerFactory $customerFactory
     * @param Json $json
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        customerFactory $customerFactory,
        Json $json
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->json = $json;
    }

    /**
     * To get the customer Data
     *
     * @return void
     */
    public function execute()
    {
        $this->getCustomerData();
    }

    /**
     * To get Customer email and phone
     *
     * @return bool|string
     */
    public function getCustomerData()
    {
        $collections = $this->collectionFactory->create()
            ->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('is_registered', ['eq' => 1]);
        $customerData = [];
        foreach ($collections as $collection) {
            $mobile_number = $collection['mobile_number'];

            $customers = $this->customerFactory->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('phone', $mobile_number);
            foreach ($customers as $customer) {
                $customerData[] = [
                    "email" => $customer->getEmail(),
                    "mobile_number" => $customer->getPhone()
                ];
            }
        }
        return $this->json->serialize($customerData);
    }
}
