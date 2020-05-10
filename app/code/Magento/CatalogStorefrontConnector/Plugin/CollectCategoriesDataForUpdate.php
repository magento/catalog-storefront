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
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataForUpdate
{
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
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryUpdatesPublisher $categoryPublisher,
        CollectionFactory $collectionFactory
    ) {
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
        array $categoryIds = []
    ): Rows {
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->addFieldToFilter('entity_id', $categoryIds);
        $categoryIdsByStore = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = (string)$category->getId();
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue ;
                }
                $categoryIdsByStore[$storeId][] = [$categoryId];

                if (true === $category->dataHasChangedFor(Category::KEY_IS_ACTIVE)) {
                    $categoryIdsByStore[$storeId][] = $category->getParentIds();
                }
            }
        }
        foreach ($categoryIdsByStore as $storeId => $storeCategoryIds) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $this->categoryPublisher->publish(
                'category_updated',
                array_unique(array_merge(...$storeCategoryIds)),
                $storeId
            );
        }
        return $result;
    }
}
