<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Sync;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Plugin\CategoryUpdatesPublisher;
use Magento\CatalogStorefrontConnector\Plugin\ProductUpdatesPublisher;

/**
 * Plugin for collect products data product save. Handle case when indexer mode is set to "runtime"
 */
class SyncStorageOnStoreSave
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryUpdatesPublisher
     */
    private $categoryPublisher;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ProductUpdatesPublisher $productPublisher
     * @param CategoryUpdatesPublisher $categoryPublisher
     * @param StoreManagerInterface $storeManager
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ProductUpdatesPublisher $productPublisher,
        CategoryUpdatesPublisher $categoryPublisher,
        StoreManagerInterface $storeManager,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        $this->productPublisher = $productPublisher;
        $this->indexerRegistry = $indexerRegistry;
        $this->categoryPublisher = $categoryPublisher;
        $this->storeManager = $storeManager;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
    }

    /**
     * Sync catalog/product for saved store.
     *
     * @param \Magento\Store\Model\Store $subject
     * @param \Magento\Store\Model\Store $result
     *
     * @return \Magento\Store\Model\Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Store\Model\Store $subject,
        \Magento\Store\Model\Store $result
    ): \Magento\Store\Model\Store {
        if ($this->isIndexerRunOnSchedule()) {
            return $result;
        }

        $storeId = (int)$result->getStoreId();

        $this->productPublisher->publish([], $storeId);
        $this->categoryPublisher->publish([], $storeId);

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
