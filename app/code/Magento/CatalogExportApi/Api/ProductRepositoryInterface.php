<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api;

/**
 * Product entity repository
 */
interface ProductRepositoryInterface
{
    /**
     * Get products by ids
     *
     * @param string[] $ids
     * @param string[] $storeViewCodes
     * @return \Magento\CatalogExportApi\Api\Data\Product[]
     */
    public function get(array $ids, array $storeViewCodes = []);
}
