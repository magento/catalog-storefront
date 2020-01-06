<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object interface for reindexed products collector
 */
interface ReindexProductsDataInterface
{
    /**
     * Set store ID for reindexed products
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;

    /**
     * Get store ID for reindexed products
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set reindexed product IDs
     *
     * @param int[] $productIds
     * @return void
     */
    public function setProductIds(array $productIds): void;

    /**
     * Get reindexed product IDs
     *
     * @return int[]
     */
    public function getProductIds(): array;
}
