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
namespace Core\OtpLogin\Model\Config;

class ResponseCodes
{
    /**
     * OTP is successfully verified
     */
    public const OTP_VARIFIED = 200;

    /**
     * OTP is expired
     */
    public const OTP_EXPIRED = 410;

    /**
     * Invalid OTP submitted by customer
     */
    public const OTP_INVALID = 409;

    /**
     * OTP verification limit is reached
     */
    public const OTP_LIMIT_REACHED = 429;

    /**
     * Invalid reference code
     */
    public const RF_CODE_INVALID = 430;
}
