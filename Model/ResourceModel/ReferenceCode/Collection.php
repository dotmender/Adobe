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

namespace Core\OtpLogin\Model\ResourceModel\ReferenceCode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Core\OtpLogin\Model\ReferenceCode as ReferenceCodeModel;
use Core\OtpLogin\Model\ResourceModel\ReferenceCode as ReferenceCodeResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Construct method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ReferenceCodeModel::class,
            ReferenceCodeResourceModel::class
        );
    }
}
