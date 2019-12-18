<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStoreFrontConnector\Model;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Consumer processes messages with store front products data
 */
class QueueConsumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;
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
     * @var string
     */
    private $topicName = 'storefront.collect.update.entities.data';
    /**
     * @var int
     */
    private $batchSize;
    /**
     * @var DataProvider
     */
    private $fulltextDataProvider;

    /**
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param DataProviderInterface $productsDataProvider
     * @param EntitiesUpdateMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param DataProvider $fulltextDataProvider
     * @param int $batchSize
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        DataProviderInterface $productsDataProvider,
        EntitiesUpdateMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        DataProvider $fulltextDataProvider,
        int $batchSize = 500
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->fulltextDataProvider = $fulltextDataProvider;
        $this->batchSize = $batchSize;
    }

    /**
     * @param ReindexProductsDataInterface[] $messages
     * @return void
     */
    public function processMessages(array $messages)
    {
        $storeProducts = $this->getUniqueIdsForStores($messages);
        foreach ($storeProducts as $storeId => $productIds) {
            $offset = 0;
            while ($messagesBunch = \array_slice($productIds, $offset, $this->batchSize)) {
                $messages = [];
                $productsData = $this->productsDataProvider->fetch($productIds, [], ['store' => $storeId]);
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
     * @param array $messages
     * @return array
     */
    private function getUniqueIdsForStores(array $messages)
    {
        $result = [];
        /** @var \Magento\CatalogStoreFrontConnector\Model\ReindexProductsData $reindexProductsData */
        foreach ($messages as $reindexProductsData) {
            $storeId = $reindexProductsData->getStoreId();
            $productIds = !empty($reindexProductsData->getProductIds())
                ? $reindexProductsData->getProductIds()
                : $this->getAllProductIdsForStore($storeId);
            $storeProductIds = isset($result[$storeId])
                ? array_merge($result[$storeId], $productIds)
                : $productIds;
            $result[$storeId] = array_unique($storeProductIds);
        }

        return $result;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getAllProductIdsForStore($storeId)
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
        while (count($products) > 0) {
            $productIds = array_column($products, 'entity_id');
            $lastProductId = end($productIds);
            $relatedProducts = $this->getRelatedProducts($products);
            $productIds = array_merge($productIds, $relatedProducts);
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
     * @param array $products
     * @return array
     */
    private function getRelatedProducts(array $products)
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
