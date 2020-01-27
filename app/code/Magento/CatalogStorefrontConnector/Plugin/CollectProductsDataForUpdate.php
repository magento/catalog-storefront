<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreDimensionProvider;

/**
 * Plugin for collect products data during reindex
 */
class CollectProductsDataForUpdate
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
     * Handle product save when indexer mode is set to "schedule"
     *
     * @param Fulltext $subject
     * @param void $result
     * @param array $dimensions
     * @param \Traversable|null $entityIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteByDimensions(
        Fulltext $subject,
        $result,
        array $dimensions,
        \Traversable $entityIds = null
    ): void {
        if (!$this->isIndexerRunOnSchedule()) {
            return ;
        }
        $productIds = $entityIds instanceof \Traversable ? $entityIds->getArrayCopy() : [];
        $this->productPublisher->publish(
            $productIds,
            (int)$dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue()
        );
    }

    /**
     * Handle product save when indexer mode is set to "realtime"
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    public function afterSave(\Magento\Catalog\Model\Product $product): void
    {
        if ($this->isIndexerRunOnSchedule()) {
            return ;
        }

        foreach ($product->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            $this->productPublisher->publish([$product->getId()], $storeId);
        }
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
