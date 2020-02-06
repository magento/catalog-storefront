<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api;

use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterface;

/**
 * Get categories by ids
 */
interface CategoryInterface
{
    /**
     * Search category by search criteria
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\CategoryCriteriaInterface[] $requests
     *
     * @return CategoryResultContainerInterface[]
     */
    public function get(array $requests): array;
}
