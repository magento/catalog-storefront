<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api;

use Magento\CatalogStorefrontApi\Api\Data\ProductResultContainerInterface;

/**
 * Product Search Storefront API
 */
interface ProductInterface
{
    /**
     * Search products by search criteria. The result is returned in the order of the received requests
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\ProductCriteriaInterface[] $requests
     *
     * @return ProductResultContainerInterface[]
     */
    public function get(array $requests): array;
}
