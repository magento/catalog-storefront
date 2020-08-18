<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Feed\Categories as CategoriesFeed;
use Magento\CatalogDataExporter\Model\Indexer\CategoryIndexerCallbackInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated categories in queue
 * TODO: Move logic to saasExport
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
     * @var CategoriesFeed
     */
    private $categoriesFeed;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @param PublisherInterface $queuePublisher
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param CategoriesFeed $categoriesFeed
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ChangedEntitiesMessageBuilder $messageBuilder,
        CategoriesFeed $categoriesFeed,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->categoriesFeed = $categoriesFeed;
        $this->messageBuilder = $messageBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        $deleted = [];
        foreach ($this->categoriesFeed->getDeletedByIds($ids) as $category) {
            $deleted[$category['storeViewCode']][] = $category['categoryId'];
            unset($ids[$category['categoryId']]);
        }

        foreach ($deleted as $storeCode => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                $this->publishMessage(
                    CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
                    $idsChunk,
                    $storeCode
                );
            }
        }

        //TODO: Add store codes to categories_updated message here? Would cause redundant calls back to saasExport though.
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            $this->publishMessage(
                CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
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
