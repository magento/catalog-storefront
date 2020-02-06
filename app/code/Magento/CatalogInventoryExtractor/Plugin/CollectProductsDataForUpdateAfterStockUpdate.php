<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryExtractor\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogStorefrontConnector\Plugin\ProductUpdatesPublisher;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Plugin for collect products data during reindex.
 */
class CollectProductsDataForUpdateAfterStockUpdate
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
     * Handle stock item save
     *
     * @param StockRegistryInterface $subject
     * @param $result
     * @param string $productSku
     * @param StockItemInterface $stockItem
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateStockItemBySku(
        StockRegistryInterface $subject,
        $result,
        string $productSku,
        StockItemInterface $stockItem
    ): ?int {
        if ($this->isIndexerRunOnSave()) {
            $this->productPublisher->publish(
                [(int)$stockItem->getProductId()],
                (int)$stockItem->getStoreId()
            );
        }

        return (int)$result;
    }

    /**
     * Is indexer run in "on schedule" mode
     *
     * @return bool
     */
    private function isIndexerRunOnSave(): bool
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        return !$indexer->isScheduled();
    }
}
