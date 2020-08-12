<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\CategoryIndexerCallbackInterface;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
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
     * @var \Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer
     */
    private $consumer;
    /**
     * @var UpdatedEntitiesMessageBuilder
     */
    private $messageBuilder;
    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Categories
     */
    private $categoriesFeed;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        StoreManagerInterface $storeManager,
        \Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer $consumer,
        \Magento\CatalogDataExporter\Model\Feed\Categories $categoriesFeed,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->consumer = $consumer;
        $this->messageBuilder = $messageBuilder;
        $this->categoriesFeed = $categoriesFeed;
        $this->storeManager = $storeManager;
    }

    /**
     * Todo: eliminate duplication of getFeedByIds here and in CategoryConsumer.
     * Either pass all updated category ids, without storeIds, or... there is no other option i think?
     * In order to check which were the deleted ids i need to make a call to the feed however. Unless i get that from the function which calls this function (indexer).
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
        if (!empty($deleted)) {
            $this->publishMessage(
                $deleted,
                \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesData::CATEGORIES_DELETED_EVENT_TYPE
            );
        }

        if (!empty($ids)) {
            $this->publishMessage(
                $ids,
                \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesData::CATEGORIES_UPDATED_EVENT_TYPE
            );
        }
    }

    /**
     * Publish deleted or updated message
     * Todo: Shorten/make the function more generic. Is there a point of sending storeId over to the consumer for deletion? Its always deleted from all stores?
     *
     * @param $data
     * @param $eventType
     */
    private function publishMessage($data, $eventType)
    {
        if ($eventType === \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesData::CATEGORIES_DELETED_EVENT_TYPE) {
            foreach ($data as $storeId => $entityIds) {
                foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                    if (!empty($idsChunk)) {
                        $message = $this->messageBuilder->buildv2(
                            ['type' => $eventType],
                            [
                                'ids' => $idsChunk,
                                'storeId' => $storeId
                            ]
                        );
                        try {
//                    $this->queuePublisher->publish(self::TOPIC_NAME, $message);
                            $this->consumer->processMessage($message);
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                }
            }
        } elseif ($eventType === \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesData::CATEGORIES_UPDATED_EVENT_TYPE) {
            foreach (array_chunk($data, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk)) {
                    $message = $this->messageBuilder->buildv2(
                        ['type' => $eventType],
                        [
                            'ids' => $idsChunk,
                        ]
                    );
                    try {
//                    $this->queuePublisher->publish(self::TOPIC_NAME, $message);
                        $this->consumer->processMessage($message);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }
            }
        }
    }

    /**
     * Resolve store ID by store code
     *
     * @param array $mappedStores
     * @param string $storeCode
     * @return int|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? 1;
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
                $storesToIds[$store->getCode()] = $store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = 1;
        }

        return $storesToIds;
    }
}
