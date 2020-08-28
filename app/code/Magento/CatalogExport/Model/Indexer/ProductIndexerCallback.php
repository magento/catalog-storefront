<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\ProductIndexerCallbackInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated products in queue
 * TODO: Move logic to Service Export
 */
class ProductIndexerCallback implements ProductIndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.product.data';

    private const PRODUCTS_UPDATED_EVENT_TYPE = 'products_updated';

    private const PRODUCTS_DELETED_EVENT_TYPE = 'products_deleted';

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
        $this->messageBuilder = $messageBuilder;
        $this->feedPool = $feedPool;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        $deleted = [];
        $productsFeed = $this->feedPool->getFeed('products');
        foreach ($productsFeed->getDeletedByIds($ids) as $product) {
            $deleted[$product['storeViewCode']][] = $product['productId'];
            $deleteProductIndex = array_search($product['productId'], $ids);
            unset($ids[$deleteProductIndex]);
        }

        foreach ($deleted as $storeCode => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                $this->publishMessage(
                    self::PRODUCTS_DELETED_EVENT_TYPE,
                    $idsChunk,
                    $storeCode
                );
            }
        }

        //TODO: Add store codes to products_updated message here?
        //Would cause redundant calls back to Service Export though.
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            $this->publishMessage(
                self::PRODUCTS_UPDATED_EVENT_TYPE,
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
