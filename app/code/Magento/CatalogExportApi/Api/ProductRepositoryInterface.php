<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api;

use Magento\CatalogExport\Api\Data\EntitiesRequestInterface;

/**
 * Product entity repository
 */
interface ProductRepositoryInterface
{
    /**
     * Get products by request
     *
     * @param \Magento\CatalogExport\Api\Data\EntitiesRequestInterface $request
     *
     * @return \Magento\CatalogExportApi\Api\Data\Product[]
     */
    public function get(EntitiesRequestInterface $request);
}
