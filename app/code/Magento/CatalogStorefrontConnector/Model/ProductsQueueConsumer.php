<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesData;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;

/**
 * Consumer processes messages with store front products data
 */
class ProductsQueueConsumer
{
    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var \Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer
     */
    private $productsConsumer;

    /**
     * @var \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer
     */
    private $productFeedIndexer;

    /**
     * @param \Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer $productsConsumer
     * @param \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer $productFeedIndexer
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        \Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer $productsConsumer,
        \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer $productFeedIndexer,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->productsConsumer = $productsConsumer;
        $this->productFeedIndexer = $productFeedIndexer;
    }

    /**
     * Process collected product IDs for update
     *
     * Process messages from storefront.catalog.product.update topic
     * and publish new messages to storefront.catalog.data.consume
     *
     * @param UpdatedEntitiesDataInterface $message
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processMessages(UpdatedEntitiesDataInterface $message): void
    {
        $storeProducts = $this->getUniqueIdsForStores([$message]);
        //TODO: remove ad-hoc solution after moving events to saas-export
        $allProductIds = [];
        foreach ($storeProducts as $storeId => $productIds) {
            if (empty($productIds)) {
                foreach ($this->catalogEntityIdsProvider->getProductIds($storeId) as $ids) {
                    $allProductIds[] = $ids;
                }
            } else {
                $allProductIds[] = $productIds;
            }
        }
        $ids = \array_unique(\array_merge(...$allProductIds));
        $this->productFeedIndexer->executeList($ids);
        $this->productsConsumer->processMessage(\json_encode($ids));
    }

    /**
     * Get unique ids for stores from messages
     *
     * @param array $messages
     * @return array
     */
    private function getUniqueIdsForStores(array $messages): array
    {
        $storesProductIds = [];
        /** @var UpdatedEntitiesData $updatedProductsData */
        foreach ($messages as $updatedProductsData) {
            $storeId = $updatedProductsData->getStoreId();
            if (empty($updatedProductsData->getEntityIds())) {
                // full reindex
                $storesProductIds[$storeId] = [];
            } elseif (isset($storesProductIds[$storeId]) && empty($storesProductIds[$storeId])) {
                continue;
            } elseif (!isset($storesProductIds[$storeId])) {
                $storesProductIds[$storeId] = $updatedProductsData->getEntityIds();
            } else {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $storesProductIds[$storeId] = array_merge(
                    $storesProductIds[$storeId],
                    $updatedProductsData->getEntityIds()
                );
            }
        }

        return $storesProductIds;
    }
}
