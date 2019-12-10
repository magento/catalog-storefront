<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProductApi\Api;

use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterface;

/**
 * Product Search StoreFront API
 */
interface ProductSearchInterface
{
    /**
     * Search products by search criteria. The result is returned in the order of the received requests
     *
     * @param \Magento\CatalogProductApi\Api\Data\ProductSearchCriteriaInterface[] $requests
     *
     * @return ProductResultContainerInterface[]
     */
    public function search(array $requests): array;
}
