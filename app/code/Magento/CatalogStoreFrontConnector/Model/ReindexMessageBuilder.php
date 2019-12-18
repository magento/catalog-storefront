<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

class ReindexMessageBuilder
{
    /**
     * @var ReindexProductsDataInterface
     */
    private $reindexProducts;

    /**
     * @param ReindexProductsDataInterface $reindexProducts
     */
    public function __construct(
        ReindexProductsDataInterface $reindexProducts
    ) {
        $this->reindexProducts = $reindexProducts;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return ReindexProductsDataInterface
     */
    public function prepareMessage($storeId, array $productIds)
    {

        $this->reindexProducts->setStoreId($storeId);
        $this->reindexProducts->setProductIds($productIds);

        return $this->reindexProducts;
    }
}
