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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataOnSave
{
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
     * @var CategoryUpdatesPublisher
     */
    private $categoryPublisher;

    /**
     * @param CollectionFactory $collectionFactory
     * @param IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resource
     * @param CategoryUpdatesPublisher $categoryPublisher
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        IndexerRegistry $indexerRegistry,
        ResourceConnection $resource,
        CategoryUpdatesPublisher $categoryPublisher
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->resource = $resource;
        $this->categoryPublisher = $categoryPublisher;
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
        $categoryIdsPerStore = [];
        foreach ($categoryCollection as $category) {
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue ;
                }
                $categoryIdsPerStore[$storeId][] = [$category->getId()];

                if (true === $category->dataHasChangedFor(Category::KEY_IS_ACTIVE)) {
                    $categoryIdsPerStore[$storeId][] = $category->getParentIds();
                }
                if (!empty($category->getChangedProductIds())) {
                    $categoryIdsPerStore[$storeId][] = $category->getChangedProductIds();
                }
            }
        }
        foreach ($categoryIdsPerStore as $storeId => $categories) {
            $this->categoryPublisher->publish(\array_merge(...$categories), $storeId);
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
}
