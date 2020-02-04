<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
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
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param ProductUpdatesPublisher $productPublisher
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        ProductUpdatesPublisher $productPublisher,
        IndexerRegistry $indexerRegistry
    ) {
        $this->productPublisher = $productPublisher;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Handle product save when indexer mode is set to "realtime"
     *
     * @param Product $subject
     * @param Product $result
     * @param AbstractModel $product
     *
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        Product $subject,
        Product $result,
        AbstractModel $product
    ): Product {
        if ($this->isIndexerRunOnSchedule()) {
            return $result;
        }

        foreach ($product->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            $this->productPublisher->publish([$product->getId()], $storeId);
        }

        return $result;
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
