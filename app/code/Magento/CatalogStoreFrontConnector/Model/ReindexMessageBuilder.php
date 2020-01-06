<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

use Magento\CatalogStoreFrontConnector\Model\Data\ReindexProductsDataInterface;

/**
 * Reindex message builder
 */
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
     * Build message for storefront.collect.reindex.products.data topic
     *
     * @param int $storeId
     * @param array $productIds
     *
     * @return ReindexProductsDataInterface
     */
    public function build($storeId, array $productIds): ReindexProductsDataInterface
    {

        $this->reindexProducts->setStoreId($storeId);
        $this->reindexProducts->setProductIds($productIds);

        return $this->reindexProducts;
    }
}
