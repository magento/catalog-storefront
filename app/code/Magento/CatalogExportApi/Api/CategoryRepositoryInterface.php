<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api;

/**
 * Category entity repository interface
 */
interface CategoryRepositoryInterface
{
    /**
     * Get categories by ids
     *
     * @param string[] $ids
     * @param string[] $storeViewCodes
     * @return \Magento\CatalogExportApi\Api\Data\Category[]
     */
    public function get(array $ids, array $storeViewCodes = []);
}
