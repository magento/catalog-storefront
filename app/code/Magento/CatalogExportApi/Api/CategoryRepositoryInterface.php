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
     * @return \Magento\CatalogExportApi\Api\Data\Category[]
     */
    public function get(array $ids);

    /**
     * Get deleted categories by ids.
     *
     * @param string[] $ids
     * @return array
     */
    public function getDeleted(array $ids): array;
}
