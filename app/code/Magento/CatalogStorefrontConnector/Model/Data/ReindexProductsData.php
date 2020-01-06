<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object for reindexed products collector
 */
class ReindexProductsData implements ReindexProductsDataInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var int[]
     */
    private $productIds;

    /**
     * @inheritdoc
     */
    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * @inheritdoc
     */
    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }

    /**
     * @inheritdoc
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }
}
