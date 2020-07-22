<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\IndexerCallbackInterface;
use Magento\CatalogMessageBroker\Model\SerializerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes ids of updated products in queue
 */
class IndexerCallback implements IndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    public const PRODUCT_ENTITY = 'product';
    public const PRODUCT_VARIANT_ENTITY = 'product_variant';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $topicMap;
    
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param PublisherInterface $queuePublisher
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param array $topicMap
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        array $topicMap
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->topicMap = $topicMap;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids, string $entityType)
    {
        $this->validateEntityType($entityType);
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                try {
                    // @todo understand why string[] doesn't work
                    $this->queuePublisher->publish(
                        $this->topicMap[$entityType],
                        $this->serializer->serialize($idsChunk)
                    );
                } catch (\Throwable $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * Check entity type on satisfaction to domain values.
     *
     * @param string $entityType
     * @return void
     */
    private function validateEntityType(string $entityType): void
    {
        if (!isset($this->topicMap[$entityType])) {
            throw new \DomainException("'$entityType' entity type doesn't supported.");
        }
    }
}
