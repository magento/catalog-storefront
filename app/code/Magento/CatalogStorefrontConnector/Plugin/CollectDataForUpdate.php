<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogStorefrontConnector\Model\ReindexMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\StoreDimensionProvider;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;

/**
 * Plugin for collect products data during reindex
 */
class CollectDataForUpdate
{
    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var ReindexMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;

    /**
     * @var string
     */
    private $topic = 'storefront.collect.reindex.products.data';

    /**
     * @param PublisherInterface $queuePublisher
     * @param ReindexMessageBuilder $messageBuilder
     * @param FulltextResource $fulltextResource
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ReindexMessageBuilder $messageBuilder,
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
    ) {
        $proceed($dimensions, $entityIds);

        $productIds = $entityIds instanceof \Traversable ? $entityIds->getArrayCopy() : [];
        // add related products only in case of partial reindex
        if ($productIds) {
            $productIds = array_unique(
                array_merge($entityIds, $this->fulltextResource->getRelationsByChild($entityIds))
            );
        }
        $storeId = (int)$dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $message = $this->messageBuilder->build($storeId, $productIds);
        $this->queuePublisher->publish($this->topic, $message);
    }
}
