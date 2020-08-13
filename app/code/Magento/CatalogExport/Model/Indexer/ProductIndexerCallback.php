<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Feed\Products as ProductsFeed;
use Magento\CatalogDataExporter\Model\Indexer\ProductIndexerCallbackInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\StoreManagerInterface;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated products in queue
 */
class ProductIndexerCallback implements ProductIndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.product.data';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ProductsConsumer
     */
    private $consumer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;
    /**
     * @var ProductsFeed
     */
    private $productsFeed;

    /**
     * @param PublisherInterface $queuePublisher
     * @param ProductsConsumer $consumer
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param ProductsFeed $productsFeed
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ProductsConsumer $consumer,
        ChangedEntitiesMessageBuilder $messageBuilder,
        ProductsFeed $productsFeed,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->consumer = $consumer;
        $this->storeManager = $storeManager;
        $this->messageBuilder = $messageBuilder;
        $this->productsFeed = $productsFeed;
    }

    /**
     * Make and publish messages for updated/deleted products.
     *
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        $storesToIds = $this->getMappedStores();

        $deleted = [];
        foreach ($this->productsFeed->getDeletedByIds($ids) as $product) {
            $storeId = $this->resolveStoreId($storesToIds, $product['storeViewCode']);
            $deleted[$storeId][] = $product['productId'];
            unset($ids[$product['productId']]);
        }

        foreach ($deleted as $storeId => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk)) {
                    $this->publishMessage(
                        ProductsConsumer::PRODUCTS_DELETED_EVENT_TYPE,
                        $idsChunk,
                        (string)$storeId
                    );
                }
            }
        }
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->publishMessage(
                    ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
                    $idsChunk,
                );
            }
        }
    }

    /**
     * Publish deleted or updated message
     *
     * @param string $eventType
     * @param int[] $ids
     * @param null|string $scope
     *
     * @return void
     */
    private function publishMessage(string $eventType, array $ids, ?string $scope = null): void
    {
        $message = $this->messageBuilder->build(
            $ids,
            $eventType,
            $scope
        );
        try {
//            $this->queuePublisher->publish(self::TOPIC_NAME, $message);
            $this->consumer->processMessage($message);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Resolve store ID by store code
     *
     * @param array $mappedStores
     * @param string $storeCode
     * @return string|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? '1';
    }

    /**
     * Retrieve mapped stores, in case if something went wrong, retrieve just one default store
     *
     * @return array
     */
    private function getMappedStores(): array
    {
        try {
            // @todo eliminate store manager
            $stores = $this->storeManager->getStores(true);
            $storesToIds = [];
            foreach ($stores as $store) {
                $storesToIds[$store->getCode()] = (string)$store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = '1';
        }

        return $storesToIds;
    }
}
