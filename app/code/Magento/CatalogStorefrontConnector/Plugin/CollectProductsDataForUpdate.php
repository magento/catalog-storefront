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
        if (!$this->isFullReindex($entityIds) && $this->isReindexOnSave()) {
            return ;
        }
        $productIds = $entityIds instanceof \Traversable ? $entityIds->getArrayCopy() : [];
        $this->productPublisher->publish(
            'product_updated',
            $productIds,
            (int)$dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue()
        );
    }

    /**
     * Is indexer run in "realtime" mode
     *
     * @return bool
     */
    private function isReindexOnSave(): bool
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        return !$indexer->isScheduled();
    }

    /**
     * Is full reindex executed
     *
     * @param \Traversable|null $entitiesIds
     * @return bool
     */
    private function isFullReindex(\Traversable $entitiesIds = null): bool
    {
        return $entitiesIds === null;
    }
}
