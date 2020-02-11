<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Throwable;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataForUpdate
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
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
        $this->resource = $resource;
        $this->indexerRegistry = $indexerRegistry;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Collect store ID and Category IDs for updated entity
     *
     * @param Rows $subject
     * @param Rows $result
     * @param array $entityIds
     * @param bool $useTempTable
     * @return Rows
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        Rows $subject,
        Rows $result,
        array $entityIds = [],
        $useTempTable = false
    ): Rows {
        if ($this->isIndexerRunOnSchedule()) {
            return $result;
        }
        $categoryIds = $entityIds;
        foreach ($categoryIds as $categoryId) {
            $parentIds = explode('/', $this->getPathFromCategoryId($categoryId));
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $categoryIds = array_merge($categoryIds, $parentIds);
        }
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->addFieldToFilter('entity_id', $categoryIds);
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = $category->getId();
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue;
                }
                $message = $this->messageBuilder->build($storeId, [$categoryId]);
                try {
                    $this->logger->debug(sprintf('Collect category id: "%s" in store %s', $categoryId, $storeId));
                    $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
                } catch (Throwable $e) {
                    $this->logger->critical(
                        sprintf('Error on collect category id "%s" in store %s', $categoryId, $storeId),
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
    private function getPathFromCategoryId($categoryId)
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
    private function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
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
}
