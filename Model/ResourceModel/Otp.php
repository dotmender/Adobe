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
namespace Core\OtpLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Customer\Model\Customer;

class Otp extends AbstractDb
{
    /**
     * Construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('otp_login', 'otp_id');
    }

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        parent::__construct($context);
        $this->_eavAttribute = $eavAttribute;
    }

    /**
     * Check Mobile Number Exist
     *
     * @param String $mobileNumber
     * @param object $store
     * @return mixed
     */
    public function checkMobileNumberAvailable($mobileNumber, $store)
    {
            $webSiteId = $store->getWebsiteId();
            $attributeId = $this->_eavAttribute->getIdByCode(Customer::ENTITY, 'phone');

            $select = $this->getConnection()->select()
                ->from(
                    ['customer_entity_varchar'],
                    ['value']
                )->where(
                    'customer_entity_varchar.value=?',
                    $mobileNumber
                )->where(
                    'eav_attribute.attribute_id=?',
                    $attributeId
                )->where(
                    'customer_entity.website_id=?',
                    $webSiteId
                )->joinLeft(
                    'customer_entity',
                    'customer_entity.entity_id = customer_entity_varchar.entity_id',
                    ['customer_entity.entity_id']
                )->joinLeft(
                    'eav_attribute',
                    'eav_attribute.attribute_id = customer_entity_varchar.attribute_id',
                    ['eav_attribute.attribute_id']
                );

            return $this->getConnection()->fetchRow($select);
    }

    /**
     * Get OTP Id
     *
     * @param String $mobileOtp
     * @return string
     */
    public function getOtpIdByOtp($mobileOtp)
    {
        $tableName = $this->getConnection()->getTableName('otp_login');

        $select = $this->getConnection()->select()
            ->from(
                ['o' => $tableName],
                ['otp_id']
            )
            ->where(
                'o.otp=?',
                $mobileOtp
            );
        $record = $this->getConnection()->fetchOne($select);
        return $record;
    }

    /**
     * Set Limit
     *
     * @param String $mobileNumber
     * @param String $interval
     * @return mixed
     */
    public function sendOtpLimit($mobileNumber, $interval)
    {
        $tableName = $this->getConnection()->getTableName('otp_login');

        $select = $this->getConnection()->select()
            ->from(
                ['o' => $tableName],
                ['otp_id']
            )->where(
                'o.mobile_number=?',
                $mobileNumber
            )->where(
                'o.is_blocked=?',
                false
            )->where(
                'o.created_at > NOW() - INTERVAL '.$interval.' MINUTE'
            )->order('o.otp_id DESC');
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Before Save Validation
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return Otp
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $tableName = $this->getConnection()->getTableName('otp_login');
        $mobileNumber = $object->getMobileNumber();
        $data = [
            "is_active"=> 0,
            "status"=> 0
        ];
        $where = ['mobile_number = ?' => $mobileNumber];
        $this->getConnection()->update($tableName, $data, $where);

        return parent::_beforeSave($object);
    }

    /**
     * Block Customer to send OTP
     *
     * @param String $otpId
     * @return void
     */
    public function blockCustomerToSendOtp($otpId)
    {
        $tableName = $this->getConnection()->getTableName('otp_login');
        $data = ["is_blocked"=> 1];
        $where = ['otp_id = ?' => $otpId];
        $this->getConnection()->update($tableName, $data, $where);
    }

    /**
     * Check Customer is blocked
     *
     * @param String $mobileNumber
     * @return mixed
     */
    public function checkCustomerIsBlocked($mobileNumber)
    {
        $tableName = $this->getConnection()->getTableName('otp_login');
        $select = $this->getConnection()->select()
            ->from(
                ['o' => $tableName],
                ['otp_id', 'created_at', 'is_blocked']
            )->where(
                'o.mobile_number=?',
                $mobileNumber
            )->order('o.otp_id DESC')->limit(1);
        return $this->getConnection()->fetchRow($select);
    }
}
