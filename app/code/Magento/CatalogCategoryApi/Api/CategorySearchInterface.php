<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategoryApi\Api;

use Magento\CatalogCategoryApi\Api\Data\CategoryResultContainerInterface;

/**
 * Category Search StoreFront API
 */
interface CategorySearchInterface
{
    /**
     * Search category by search criteria
     *
     * @param \Magento\CatalogCategoryApi\Api\Data\CategorySearchCriteriaInterface[] $requests
     *
     * @return CategoryResultContainerInterface[]
     */
    public function search(array $requests): array;
}
