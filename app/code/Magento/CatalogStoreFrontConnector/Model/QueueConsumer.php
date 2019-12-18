<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStoreFrontConnector\Model;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\CatalogStoreFrontConnector\Model\Data\ReindexProductsDataInterface;

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
     * @var DataProvider
     */
    private $fulltextDataProvider;

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
     * @param DataProvider $fulltextDataProvider
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        EntitiesUpdateMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        DataProvider $fulltextDataProvider,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->fulltextDataProvider = $fulltextDataProvider;
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
            $offset = 0;
            while ($idsBunch = \array_slice($productIds, $offset, $this->batchSize)) {
                $messages = [];
                $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
                foreach ($productsData as $product) {
                    $messages[] = $this->messageBuilder->prepareMessage(
                        (int)$storeId,
                        $product['type_id'],
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
        /** @var \Magento\CatalogStoreFrontConnector\Model\Data\ReindexProductsData $reindexProductsData */
        foreach ($messages as $reindexProductsData) {
            $storeId = $reindexProductsData->getStoreId();
            $productIds = !empty($reindexProductsData->getProductIds())
                ? $reindexProductsData->getProductIds()
                : $this->getAllProductIdsForStore($storeId);
            $storeProductIds = isset($result[$storeId])
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                ? \array_merge($result[$storeId], $productIds)
                : $productIds;
            $result[$storeId] = \array_unique($storeProductIds);
        }

        return $result;
    }

    /**
     * Get all product IDs assigned to store
     *
     * @param int $storeId
     * @return array
     */
    private function getAllProductIdsForStore(int $storeId): array
    {
        $productIds = [];
        $lastProductId = 0;
        $products = $this->fulltextDataProvider->getSearchableProducts(
            $storeId,
            [],
            null,
            $lastProductId,
            $this->batchSize
        );
        while (\count($products) > 0) {
            $productIds = \array_column($products, 'entity_id');
            $lastProductId = \end($productIds);
            $relatedProducts = $this->getRelatedProducts($products);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $productIds = \array_merge($productIds, $relatedProducts);
            $products = $this->fulltextDataProvider->getSearchableProducts(
                $storeId,
                [],
                null,
                $lastProductId,
                $this->batchSize
            );
        }

        return $productIds;
    }

    /**
     * Get related products for provided array of products data
     *
     * @param array $products
     * @return array
     */
    private function getRelatedProducts(array $products): array
    {
        $relatedProducts = [];
        foreach ($products as $productData) {
            $relatedProducts[$productData['entity_id']] = $this->fulltextDataProvider->getProductChildIds(
                $productData['entity_id'],
                $productData['type_id']
            );
        }
        $relatedProducts = array_filter($relatedProducts);
        $relatedIds = !empty($relatedProducts) ? array_merge(...$relatedProducts) : [];
        
        return $relatedIds;
    }
}
