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

use Core\OtpLogin\Model\ResourceModel\ReferenceCode as ReferenceCodeResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Math\Random;
use Core\OtpLogin\Helper\Data as OtpHelper;

class ReferenceCode extends AbstractModel
{
    protected const XML_MIN_RC_DIGIT = 10;
    protected const XML_MAX_RC_DIGIT = 99;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * Construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ReferenceCodeResourceModel::class);
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Random $mathRandom
     * @param OtpHelper $otpHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Random $mathRandom,
        OtpHelper $otpHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->mathRandom = $mathRandom;
        $this->otpHelper = $otpHelper;
    }

    /**
     * Generate Reference Code
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateReferenceCode()
    {
        $testReferenceCode = $this->otpHelper->getTestReferenceCode();
        if ($testReferenceCode) {
            return $testReferenceCode;
        }

        return $this->mathRandom->getRandomNumber(
            self::XML_MIN_RC_DIGIT,
            self::XML_MAX_RC_DIGIT
        );
    }
}
