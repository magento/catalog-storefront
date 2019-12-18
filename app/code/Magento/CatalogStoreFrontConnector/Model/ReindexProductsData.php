<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

class ReindexProductsData implements ReindexProductsDataInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $productIds;

    /**
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * @param array $productIds
     *
     * @return void
     */
    public function setProductIds(array $productIds)
    {
        $this->productIds = $productIds;
    }

    /**
     * @inheritDoc
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }
}