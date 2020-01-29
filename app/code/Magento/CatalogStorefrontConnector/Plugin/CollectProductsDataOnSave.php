<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Store\Model\Store;

/**
 * Plugin for collect products data product save. Handle case when indexer mode is set to "runtime"
 */
class CollectProductsDataOnSave
{
    /**
     * @var ProductUpdatesPublisher
     */
    private $productPublisher;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param ProductUpdatesPublisher $productPublisher
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        ProductUpdatesPublisher $productPublisher,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->productPublisher = $productPublisher;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Handle product save when indexer mode is set to "realtime"
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave(\Magento\Catalog\Model\Product $product): \Magento\Catalog\Model\Product
    {
        if ($this->isIndexerRunOnSchedule()) {
            return $product;
        }

        foreach ($product->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            $this->productPublisher->publish([$product->getId()], $storeId);
        }

        return $product;
    }

    /**
     * Is indexer run in "on schedule" mode
     *
     * @return bool
     */
    private function isIndexerRunOnSchedule(): bool
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        return $indexer->isScheduled();
    }
}
