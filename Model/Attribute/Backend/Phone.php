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

namespace Core\OtpLogin\Model\Attribute\Backend;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class Phone extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Generate and set unique Phone to customer
     *
     * @param Customer $object
     * @return void
     */
    protected function checkUniquePhone($object)
    {
        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        while (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            throw new NoSuchEntityException(__('Account with this Mobile Number is already exist'));
        }
    }

    /**
     * Make phone number unique before save
     *
     * @param Customer $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->checkUniquePhone($object);
        return parent::beforeSave($object);
    }
}
