<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
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
     * @var array
     */
    private $categoryPath;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param ProductUpdatesPublisher $productUpdatesPublisher
     * @param IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resource
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        ProductUpdatesPublisher $productUpdatesPublisher,
        CollectionFactory $collectionFactory,
        IndexerRegistry $indexerRegistry,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->productUpdatesPublisher = $productUpdatesPublisher;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
        $this->resource = $resource;
    }

    /**
     * Collect store ID and Category IDs for updated entity
     *
     * @param CategoryResource $subject
     * @param CategoryResource $result
     * @param AbstractModel $currentCategory
     * @return CategoryResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CategoryResource $subject,
        CategoryResource $result,
        AbstractModel $currentCategory
    ): CategoryResource {
        $categoryId = (string)$currentCategory->getId();
        $categoryIds = explode('/', $this->getPathFromCategoryId($categoryId));
        $categoryIds[] = $categoryId;
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->addFieldToFilter('entity_id', $categoryIds);
        foreach ($categoryCollection as $category) {
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue ;
                }
                try {
                    $this->logger->debug(
                        \sprintf('Collect category id: "%s" in store %s', $category->getId(), $storeId)
                    );
                    $this->publishCategoryMessage($category->getId(), $storeId);
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
                        \sprintf('Error on collect category id "%s" in store %s', $category->getId(), $storeId),
                        ['exception' => $e]
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Return category path by id
     *
     * @param int $categoryId
     * @return string
     */
    private function getPathFromCategoryId($categoryId): string
    {
        if (!isset($this->categoryPath[$categoryId])) {
            $categoryPath = $this->getConnection()->fetchOne(
                $this->getConnection()->select()->from(
                    $this->getTable('catalog_category_entity'),
                    ['path']
                )->where(
                    'entity_id = ?',
                    $categoryId
                )
            );

            $this->categoryPath[$categoryId] = $categoryPath ?: '';
        }
        return $this->categoryPath[$categoryId];
    }

    /**
     * @param string|string[] $table
     * @return string
     */
    private function getTable($table): string
    {
        return $this->resource->getTableName($table);
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resource->getConnection();
    }

    /**
     * Is indexer run in "on schedule" mode
     *
     * @return bool
     */
    private function isIndexerRunOnSchedule(): bool
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        return $indexer->isScheduled();
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
