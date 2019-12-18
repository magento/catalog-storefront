<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

interface ReindexProductsDataInterface
{
    /**
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId(int $storeId);

    /**
     * Get store ID for products reindex
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param array $productIds
     *
     * @return void
     */
    public function setProductIds(array $productIds);

    /**
     * Get product IDs for reindex
     *
     * @return int[]
     */
    public function getProductIds(): array;
}
