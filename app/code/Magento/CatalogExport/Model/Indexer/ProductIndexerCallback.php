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
 * Publishes ids of updated products in queue
 * TODO: Move logic to Service Export
 */
class ProductIndexerCallback implements FeedIndexerCallbackInterface
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
    public function execute(array $entityData) : void
    {
        // TODO validate callback data structure
        // Income message
        // Changed attributes
        //    [
        //        [
        //            'productId' => 4,
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
        //            'productId' => 4,
        //            'storeViewCode' => 'second_store_view',
        //        ],
        //    ];

        $deleted = [];
        $productsFeed = $this->feedPool->getFeed('products');
        foreach ($productsFeed->getDeletedByIds(\array_column($entityData, 'productId')) as $product) {
            $deleted[$product['storeViewCode']][] = ['entity_id' => (int)$product['productId']];

            foreach (\array_keys(\array_column($entityData, 'productId'), $product['productId']) as $key) {
                unset($entityData[$key]);
            }
        }

        foreach ($deleted as $storeCode => $entityIds) {
            foreach (\array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                $this->publishMessage(
                    self::PRODUCTS_DELETED_EVENT_TYPE,
                    $idsChunk,
                    $storeCode
                );
            }
        }

        $productsArray = [];

        foreach ($entityData as $productData) {
            $productsArray[$productData['storeViewCode']][] = [
                'entity_id' => (int)$productData['productId'],
                'attributes' => $productData['attributes'] ?? [],
            ];
        }

        foreach ($productsArray as $storeCode => $products) {
            foreach (\array_chunk($products, self::BATCH_SIZE) as $chunk) {
                $this->publishMessage(
                    self::PRODUCTS_UPDATED_EVENT_TYPE,
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
