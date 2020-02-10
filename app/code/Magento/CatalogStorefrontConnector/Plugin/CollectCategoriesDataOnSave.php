<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\Category;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataOnSave
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
     * @var ProductUpdatesPublisher
     */
    private $productUpdatesPublisher;


    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param ProductUpdatesPublisher $productUpdatesPublisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        ProductUpdatesPublisher $productUpdatesPublisher,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
        $this->productUpdatesPublisher = $productUpdatesPublisher;
    }

    /**
     * Collect store ID and Category IDs for updated entity
     *
     * @param CategoryResource $subject
     * @param CategoryResource $result
     * @param AbstractModel $category
     * @return CategoryResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CategoryResource $subject,
        CategoryResource $result,
        AbstractModel $category
    ): CategoryResource {
        $entityId = $category->getId();

        foreach ($category->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            try {
                $this->logger->debug(\sprintf('Collect category id: "%s" in store %s', $entityId, $storeId));
                $this->publishCategoryMessage($entityId, $storeId);
                if (true === $category->dataHasChangedFor(Category::KEY_IS_ACTIVE)) {
                    $parentCategoryIds = $category->getParentIds();
                    foreach ($parentCategoryIds as $parentCategoryId) {
                        $this->publishCategoryMessage($parentCategoryId, $storeId);
                    }
                }
                if (!empty($category->getChangedProductIds())) {
                    $this->productUpdatesPublisher->publish($category->getChangedProductIds(), $storeId);
                }

            } catch (\Throwable $e) {
                $this->logger->critical(
                    \sprintf('Error on collect category id "%s" in store %s', $entityId, $storeId),
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    /**
     * @param $entityId
     * @param int $storeId
     */
    private function publishCategoryMessage($entityId, int $storeId): void
    {
        $message = $this->messageBuilder->build($storeId, [$entityId]);
        $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
    }
}
