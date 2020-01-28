<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Psr\Log\LoggerInterface;

/**
 * Publish product updates to the internal queue
 */
class ProductUpdatesPublisher
{
    /**
     * Queue topic name
     */
    private const QUEUE_TOPIC = 'storefront.catalog.product.update';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var UpdatedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Hold processed product ids to prevent creation of message duplicate
     *
     * @var array
     */
    private $processedIds = [];

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param FulltextResource $fulltextResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        FulltextResource $fulltextResource,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->fulltextResource = $fulltextResource;
        $this->logger = $logger;
    }

    /**
     * Collect store ID and product IDs for scope of reindexed products
     *
     * @param array $productIds
     * @param int $storeId
     * @return void
     */
    public function publish(array $productIds, int $storeId): void
    {
        if (!empty($productIds) && empty(\array_diff($productIds, $this->processedIds))) {
            return ;
        }
        // add related products only in case of partial reindex
        if ($productIds) {
            $productIds = array_unique(
                array_merge($productIds, $this->fulltextResource->getRelationsByChild($productIds))
            );
        }
        $message = $this->messageBuilder->build($storeId, $productIds);
        try {
            $this->logger->debug(\sprintf('Collect product ids: "%s"', \implode(', ', $productIds)));
            $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
            $this->processedIds = $productIds;
        } catch (\Throwable $e) {
            $this->logger->critical(
                \sprintf('Error on collect product ids "%s"', \implode(', ', $productIds)),
                ['exception' => $e]
            );
        }
    }
}
