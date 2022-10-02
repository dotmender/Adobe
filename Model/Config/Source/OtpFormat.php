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

declare (strict_types=1);

namespace Core\OtpLogin\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Environment for configuration
 */
class OtpFormat implements ArrayInterface
{
    /**
     * @var string
     */
    private const FORMAT_NUMERIC = 'numeric';

    /**
     * @var string
     */
    private const FORMAT_ALPHABETIC = 'alphabetic';

    /**
     * @var string
     */
    private const FORMAT_ALPHANUMERIC = 'alphanumeric';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FORMAT_NUMERIC, 'label' => __('Numeric')],
            ['value' => self::FORMAT_ALPHABETIC, 'label' => __('Alphabetic')],
            ['value' => self::FORMAT_ALPHANUMERIC, 'label' => __('Alphanumeric')]
        ];
    }
}
