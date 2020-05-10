<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Publish category updates to the internal queue
 */
class CategoryUpdatesPublisher
{
    /**
     * Queue topic name
     */
    private const QUEUE_TOPIC = 'storefront.catalog.category.update';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var UpdatedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
    }

    /**
     * Collect store ID and category IDs for scope of reindexed categories
     *
     * @param string $eventType
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     */
    public function publish(string $eventType, array $categoryIds, int $storeId): void
    {
        if ($storeId === Store::DEFAULT_STORE_ID) {
            return;
        }
        $message = $this->messageBuilder->build($eventType, $storeId, $categoryIds);
        try {
            $this->logger->debug(
                \sprintf('Collect category ids: "%s" in store %s', \implode(', ', $categoryIds), $storeId)
            );
            $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
        } catch (\Throwable $e) {
            $this->logger->critical(
                \sprintf('Error on collect category ids "%s" in store %s', \implode(', ', $categoryIds), $storeId),
                ['exception' => $e]
            );
        }
    }
}
