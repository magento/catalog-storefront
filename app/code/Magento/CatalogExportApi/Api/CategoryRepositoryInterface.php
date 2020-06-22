<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api;

/**
 * Product entity repository
 */
interface CategoryRepositoryInterface
{
    /**
     * Get products by ids
     *
     * @param string[] $ids
     * @return \Magento\CatalogExportApi\Api\Data\CategoryInterface[]
     */
    public function get(array $ids);
}
