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
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductUpdatesPublisher $productPublisher
     * @param IndexerRegistry $indexerRegistry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductUpdatesPublisher $productPublisher,
        IndexerRegistry $indexerRegistry,
        StoreManagerInterface $storeManager
    ) {
        $this->productPublisher = $productPublisher;
        $this->indexerRegistry = $indexerRegistry;
        $this->storeManager = $storeManager;
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

        $stores = $product->getStoreId() == Store::DEFAULT_STORE_ID
            ? $product->getStoreIds()
            : [$product->getStoreId()];
        foreach ($stores as $storeId) {
            $storeId = (int)$storeId;
            $this->productPublisher->publish([$product->getId()], $storeId);
        }

        return $result;
    }

    /**
     * Handle product delete when indexer mode is set to "realtime"
     *
     * @param Product $subject
     * @param Product $result
     * @param AbstractModel $product
     *
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        Product $subject,
        Product $result,
        AbstractModel $product
    ): Product {
        if ($this->isIndexerRunOnSchedule()) {
            return $result;
        }

        foreach ($this->storeManager->getStores() as $store) {
            $this->productPublisher->publish([$product->getId()], (int)$store->getId());
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
