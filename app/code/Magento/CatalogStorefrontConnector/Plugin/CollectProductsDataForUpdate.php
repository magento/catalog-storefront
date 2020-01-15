<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;

/**
 * Plugin for collect products data during reindex
 */
class CollectProductsDataForUpdate
{
    /**
     * Queue topic name
     */
    private const QUEUE_TOPIC = 'storefront.catalog.data.updates';

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
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param FulltextResource $fulltextResource
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        FulltextResource $fulltextResource
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->fulltextResource = $fulltextResource;
    }

    /**
     * Collect store ID and product IDs for scope of reindexed products
     *
     * @param Fulltext $subject
     * @param \Closure $proceed
     * @param array $dimensions
     * @param \Traversable|null $entityIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteByDimensions(
        Fulltext $subject,
        \Closure $proceed,
        array $dimensions,
        \Traversable $entityIds = null
    ): void {
        $proceed($dimensions, $entityIds);

        $productIds = $entityIds instanceof \Traversable ? $entityIds->getArrayCopy() : [];
        // add related products only in case of partial reindex
        if ($productIds) {
            $productIds = array_unique(
                array_merge($productIds, $this->fulltextResource->getRelationsByChild($productIds))
            );
        }
        $storeId = (int)$dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $message = $this->messageBuilder->build($storeId, $productIds);
        $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
    }
}
