<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataForUpdate
{
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
     * @param CategoryUpdatesPublisher $categoryPublisher
     * @param IndexerRegistry $indexerRegistry
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryUpdatesPublisher $categoryPublisher,
        IndexerRegistry $indexerRegistry,
        CollectionFactory $collectionFactory
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->categoryPublisher = $categoryPublisher;
    }

    /**
     * Collect store ID and Category IDs for updated entity
     *
     * @param Rows $subject
     * @param Rows $result
     * @param array $categoryIds
     * @param bool $useTempTable
     * @return Rows
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        Rows $subject,
        Rows $result,
        array $categoryIds = [],
        $useTempTable = false
    ): Rows {
        if ($this->isIndexerRunOnSchedule()) {
            return $result;
        }
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->addFieldToFilter('entity_id', $categoryIds);
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = (string)$category->getId();
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue ;
                }
                $categoryIds = [$categoryId];

                if (true === $category->dataHasChangedFor(Category::KEY_IS_ACTIVE)) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $categoryIds = array_merge($categoryIds, $category->getParentIds());
                }
                $this->categoryPublisher->publish($categoryIds, $storeId);
            }
        }
        return $result;
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
