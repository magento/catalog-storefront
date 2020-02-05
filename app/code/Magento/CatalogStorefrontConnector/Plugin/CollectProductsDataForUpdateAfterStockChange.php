<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Store\Model\StoreDimensionProvider;

/**
 * Plugin for collect products data during reindex. Handle case when indexer mode is set to "schedule"
 */
class CollectProductsDataForUpdateAfterStockChange
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
    public function afterUpdateStockItemBySku(
        \Magento\CatalogInventory\Api\StockRegistryInterface $subject,
        $result,
        string $productSku,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
    ): void {
        if ($this->isIndexerRunOnSchedule()) {
            return;
        }
        $this->productPublisher->publish(
            [(int)$stockItem->getProductId()],
            (int)$stockItem->getStoreId()
        );
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
