<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api;

use Magento\CatalogExportApi\Api\Data\VariantInterface;

/**
 * Product variants repository interface.
 */
interface VariantRepositoryInterface
{
    /**
     * Get variants by ids.
     *
     * @param string[] $ids
     * @return VariantInterface[]
     * @throws \InvalidArgumentException
     */
    public function get(array $ids): array;
}
