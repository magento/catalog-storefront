<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\CatalogStorefrontConnector\Model\Data\ReindexProductsDataInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Consumer processes messages with store front products data
 */
class QueueConsumer
{
    /**
     * @var CollectionFactory
     */
    private $productsCollectionFactory;

    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductPublisher $productPublisher
     * @param CollectionFactory $productsCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductPublisher $productPublisher,
        CollectionFactory $productsCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->productPublisher = $productPublisher;
        $this->storeManager = $storeManager;
    }

    /**
     * Process collected product IDs for update
     *
     * Process messages from storefront.collect.reindex.products.data topic
     * and publish new messages to storefront.collect.update.entities.data topic
     *
     * @param ReindexProductsDataInterface[] $messages
     * @return void
     * @throws \Exception
     */
    public function processMessages(array $messages): void
    {
        $storeProducts = $this->getUniqueIdsForStores($messages);
        foreach ($storeProducts as $storeId => $productIds) {
            $this->productPublisher->publish($productIds, $storeId);
        }
    }

    /**
     * Get unique ids for stores from messages
     *
     * @param array $messages
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getUniqueIdsForStores(array $messages): array
    {
        $result = [];
        $storesProductIds = [];
        /** @var \Magento\CatalogStorefrontConnector\Model\Data\ReindexProductsData $reindexProductsData */
        foreach ($messages as $reindexProductsData) {
            $storeId = $reindexProductsData->getStoreId();
            if (empty($reindexProductsData->getProductIds())) {
                // full reindex
                $storesProductIds[$storeId] = [];
            } elseif (isset($storesProductIds[$storeId]) && empty($storesProductIds[$storeId])) {
                continue;
            } elseif (!isset($storesProductIds[$storeId])) {
                $storesProductIds[$storeId] = $reindexProductsData->getProductIds();
            } else {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $storesProductIds[$storeId] = array_merge(
                    $storesProductIds[$storeId],
                    $reindexProductsData->getProductIds()
                );
            }
        }
        foreach ($storesProductIds as $storeId => $productIds) {
            $productIds = !empty($productIds)
                ? $productIds
                : $this->getAllProductIdsForStore($storeId);
            $result[$storeId] = \array_unique($productIds);
        }

        return $result;
    }

    /**
     * Get all product IDs assigned to store
     *
     * @param int $storeId
     * @return int[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAllProductIdsForStore(int $storeId): array
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $productsCollection = $this->productsCollectionFactory->create();
        $productsCollection->addWebsiteFilter($websiteId);

        return $productsCollection->getAllIds();
    }
}
