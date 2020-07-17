<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\CategoryIndexerCallbackInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\Framework\MessageQueue\PublisherInterface;
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
    private $categoriesConsumer;

    /**
     * @param PublisherInterface $queuePublisher
     * @param CategoriesConsumer $categoriesConsumer
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        CategoriesConsumer $categoriesConsumer,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->categoriesConsumer = $categoriesConsumer;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                try {
                    $this->categoriesConsumer->processMessage(json_encode($idsChunk));
                    // @todo understand why string[] doesn't work
                    $this->queuePublisher->publish(self::TOPIC_NAME, json_encode($idsChunk));
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }
}
