<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ServicesConnector\Api;

/**
 * Validates the key for provided extension on provided environment
 */
interface KeyValidationInterface
{
    /**
     * Validates if key is present and works on api gateway
     *
     * @param string $extension
     * @param string $environment (production|sandbox)
     * @return bool
     * @throws \Magento\ServicesConnector\Api\KeyNotFoundException
     * @throws \InvalidArgumentException
     */
    public function execute($extension, $environment = 'production');
}
