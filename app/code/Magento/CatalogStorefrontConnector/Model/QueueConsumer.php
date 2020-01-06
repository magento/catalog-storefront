<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\CatalogStorefrontConnector\Model\Data\ReindexProductsDataInterface;

/**
 * Consumer processes messages with store front products data
 */
class QueueConsumer
{
    /**
     * @var DataProviderInterface
     */
    private $productsDataProvider;

    /**
     * @var EntitiesUpdateMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var Collection
     */
    private $productsCollection;

    /**
     * @var string
     */
    private $topicName = 'storefront.collect.update.entities.data';

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param DataProviderInterface $productsDataProvider
     * @param EntitiesUpdateMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param Collection $productsCollection
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        EntitiesUpdateMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        Collection $productsCollection,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->productsCollection = $productsCollection;
        $this->batchSize = $batchSize;
    }

    /**
     * Process collected product IDs for update
     *
     * Process messages from storefront.collect.reindex.products.data topic
     * and publish new messages to storefront.collect.update.entities.data topic
     *
     * @param ReindexProductsDataInterface[] $messages
     * @return void
     */
    public function processMessages(array $messages): void
    {
        $storeProducts = $this->getUniqueIdsForStores($messages);
        foreach ($storeProducts as $storeId => $productIds) {
            foreach (\array_chunk($productIds, $this->batchSize) as $idsBunch) {
                $messages = [];
                $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
                foreach ($productsData as $product) {
                    $messages[] = $this->messageBuilder->build(
                        (int)$storeId,
                        'product',
                        (int)$product['entity_id'],
                        $product
                    );
                }
                $this->queuePublisher->publish($this->topicName, $messages);
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
        $result = [];
        $storesProductIds = [];
        /** @var \Magento\CatalogStorefrontConnector\Model\Data\ReindexProductsData $reindexProductsData */
        foreach ($messages as $reindexProductsData) {
            $storeId = $reindexProductsData->getStoreId();

            if (isset($storesProductIds[$storeId]) && empty($reindexProductsData->getProductIds())) {
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
     */
    private function getAllProductIdsForStore(int $storeId): array
    {
        $storeProductIds = [];
        $lastProductId = 0;
        $this->productsCollection->setStoreId($storeId);

        while ($productIds = $this->productsCollection->getAllIds($this->batchSize, $lastProductId)) {
            $lastProductId = \end($productIds);
            $storeProductIds = \array_merge($storeProductIds, $productIds);
        }

        return $storeProductIds;
    }
}
