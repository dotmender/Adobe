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

class EncryptOtp
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Construct Method
     *
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt OTP
     *
     * @param  string $otp
     * @return string
     */
    public function encrypt($otp)
    {
        $encrypt =  $this->encryptor->encrypt($otp);
        return $encrypt;
    }

    /**
     * Decrypt Encrypted OTP
     *
     * @param  string $encryptedOtp
     * @return string
     */
    public function decrypt($encryptedOtp)
    {
        return $this->encryptor->decrypt($encryptedOtp);
    }
}
