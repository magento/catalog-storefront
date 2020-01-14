<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\Category;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataForUpdate
{
    /**
     * Queue topic name
     */
    private const QUEUE_TOPIC = 'storefront.collect.updated.categories.data';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var UpdatedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param FulltextResource $fulltextResource
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
    }

    /**
     * Collect store ID and Category IDs for updated entity
     *
     * @param Category $subject
     * @param Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        Category $subject,
        Category $category
    ) {
        $entityId = $category->getId();
        foreach ($category->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId !== 0) {
                $message = $this->messageBuilder->build($storeId, [$entityId]);
                $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
            }
        }
    }
}
