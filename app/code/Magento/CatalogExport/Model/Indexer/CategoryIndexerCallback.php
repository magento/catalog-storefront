<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\CategoryIndexerCallbackInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated categories in queue
 */
class CategoryIndexerCallback implements CategoryIndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.category.data';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CategoriesConsumer
     */
    private $consumer;
    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Categories
     */
    private $categoriesFeed;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @param PublisherInterface $queuePublisher
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param StoreManagerInterface $storeManager
     * @param CategoriesConsumer $consumer
     * @param \Magento\CatalogDataExporter\Model\Feed\Categories $categoriesFeed
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ChangedEntitiesMessageBuilder $messageBuilder,
        StoreManagerInterface $storeManager,
        CategoriesConsumer $consumer,
        \Magento\CatalogDataExporter\Model\Feed\Categories $categoriesFeed,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->consumer = $consumer;
        $this->categoriesFeed = $categoriesFeed;
        $this->storeManager = $storeManager;
        $this->messageBuilder = $messageBuilder;
    }

    /**
     * Make and publish messages for updated/deleted categories.
     *
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        $storesToIds = $this->getMappedStores();

        $deleted = [];
        foreach ($this->categoriesFeed->getDeletedByIds($ids) as $category) {
            $storeId = $this->resolveStoreId($storesToIds, $category['storeViewCode']);
            $deleted[$storeId][] = $category['categoryId'];
            unset($ids[$category['categoryId']]);
        }

        foreach ($deleted as $storeId => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk)) {
                    $this->publishMessage(
                        CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
                        $idsChunk,
                        (string)$storeId
                    );
                }
            }
        }
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->publishMessage(
                    CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
                    $idsChunk,
                );
            }
        }
    }

    /**
     * Publish deleted or updated message
     * Todo: Remove the temporary queue workaround
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
