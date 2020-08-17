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
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Publishes ids of updated products in queue
 * TODO: Move logic to saasExport
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
     * @var ProductsFeed
     */
    private $productsFeed;

    /**
     * @param PublisherInterface $queuePublisher
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param ProductsFeed $productsFeed
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ChangedEntitiesMessageBuilder $messageBuilder,
        ProductsFeed $productsFeed,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->messageBuilder = $messageBuilder;
        $this->productsFeed = $productsFeed;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids): void
    {
        $deleted = [];
        foreach ($this->productsFeed->getDeletedByIds($ids) as $product) {
            $deleted[$product['storeViewCode']][] = $product['productId'];
            unset($ids[$product['productId']]);
        }

        foreach ($deleted as $storeCode => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk)) {
                    $this->publishMessage(
                        self::PRODUCTS_DELETED_EVENT_TYPE,
                        $idsChunk,
                        $storeCode
                    );
                }
            }
        }

        //TODO: Add store codes to products_updated message here? Would cause redundant calls back to saasExport though.
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->publishMessage(
                    self::PRODUCTS_UPDATED_EVENT_TYPE,
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
            $this->queuePublisher->publish(self::TOPIC_NAME, $message);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
