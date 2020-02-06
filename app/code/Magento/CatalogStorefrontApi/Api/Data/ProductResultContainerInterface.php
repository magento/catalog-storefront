<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Container with response for Product Search Storefront API request
 *
 * @see \Magento\CatalogStorefrontApi\Api\ProductInterface
 */
interface ProductResultContainerInterface
{
    /**
     * Product data
     *
     * @return string[]
     */
    public function getItems(): array;

    /**
     * List of error messages in case of failure
     *
     * @return string[]
     */
    public function getErrors(): array;
}
