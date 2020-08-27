<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\CategoryIndexerCallbackInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated categories in queue
 * TODO: Move logic to Service Export
 */
class CategoryIndexerCallback implements CategoryIndexerCallbackInterface
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
    public function execute(array $ids): void
    {
        $deleted = [];
        $categoriesFeed = $this->feedPool->getFeed('categories');
        foreach ($categoriesFeed->getDeletedByIds($ids) as $category) {
            $deleted[$category['storeViewCode']][] = $category['categoryId'];
            unset($ids[$category['categoryId']]);
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

        //TODO: Add store codes to categories_updated message?
        //Would cause redundant calls back to Service Export though.
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            $this->publishMessage(
                self::CATEGORIES_UPDATED_EVENT_TYPE,
                $idsChunk,
            );
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
            $this->queuePublisher->publish(self::TOPIC_NAME, $message);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
