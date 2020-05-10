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
     * @param ProductPublisher $productPublisher
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        ProductPublisher $productPublisher,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        $this->productPublisher = $productPublisher;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
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
        $eventType = $message->getType();
        $storeProducts = $this->getUniqueIdsForStores([$message]);
        foreach ($storeProducts as $storeId => $productIds) {
            if (empty($productIds)) {
                foreach ($this->catalogEntityIdsProvider->getProductIds($storeId) as $ids) {
                    $this->productPublisher->publish($eventType, $ids, $storeId);
                }
            } else {
                $this->productPublisher->publish($eventType, \array_unique($productIds), $storeId);
            }
        }
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
