<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\DataExporter\Model\FeedPool;
use Magento\DataExporter\Model\Indexer\FeedIndexerCallbackInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated categories in queue
 * TODO: Move logic to Service Export
 */
class CategoryIndexerCallback implements FeedIndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.category.data';

    private const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';

    private const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var FeedPool
     */
    private $feedPool;

    /**
     * @param PublisherInterface $queuePublisher
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param FeedPool $feedPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ChangedEntitiesMessageBuilder $messageBuilder,
        FeedPool $feedPool,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->messageBuilder = $messageBuilder;
        $this->feedPool = $feedPool;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entityData) : void
    {
        // TODO validate callback data structure
        // Income message
        // Changed attributes
        //    [
        //        [
        //            'categoryId' => 4,
        //            'storeViewCode' => 'second_store_view',
        //            'attributes' => [
        //                'name',
        //                'visibility',
        //            ],
        //        ],
        //    ];
        //
        // New product / full reindex
        //    [
        //        [
        //            'categoryId' => 4,
        //            'storeViewCode' => 'second_store_view',
        //        ],
        //    ];

        $deleted = [];
        $productsFeed = $this->feedPool->getFeed('categories');
        foreach ($productsFeed->getDeletedByIds(\array_column($entityData, 'categoryId')) as $category) {
            $deleted[$category['storeViewCode']][] = ['entity_id' => (int)$category['categoryId']];

            foreach (\array_keys(\array_column($entityData, 'categoryId'), $category['categoryId']) as $key) {
                unset($entityData[$key]);
            }
        }

        foreach ($deleted as $storeCode => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                $this->publishMessage(
                    self::CATEGORIES_DELETED_EVENT_TYPE,
                    $idsChunk,
                    $storeCode
                );
            }
        }

        $categoriesArray = [];

        foreach ($entityData as $categoryData) {
            $categoriesArray[$categoryData['storeViewCode']][] = [
                'entity_id' => (int)$categoryData['categoryId'],
                'attributes' => $categoryData['attributes'] ?? [],
            ];
        }

        foreach ($categoriesArray as $storeCode => $categories) {
            foreach (\array_chunk($categories, self::BATCH_SIZE) as $chunk) {
                $this->publishMessage(
                    self::CATEGORIES_UPDATED_EVENT_TYPE,
                    $chunk,
                    $storeCode
                );
            }
        }
    }

    /**
     * Publish deleted or updated message
     *
     * @param string $eventType
     * @param array $products
     * @param string $scope
     *
     * @return void
     */
    private function publishMessage(string $eventType, array $products, string $scope): void
    {
        $message = $this->messageBuilder->build($eventType, $products, $scope);

        try {
            $this->queuePublisher->publish(self::TOPIC_NAME, $message);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
