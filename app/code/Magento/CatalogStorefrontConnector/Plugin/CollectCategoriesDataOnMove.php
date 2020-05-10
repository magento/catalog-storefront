<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;

/**
 * Plugin for update categories on move event
 */
class CollectCategoriesDataOnMove
{
    /**
     * @var CategoryUpdatesPublisher
     */
    private $categoryPublisher;

    /**
     * @param CategoryUpdatesPublisher $categoryPublisher
     */
    public function __construct(
        CategoryUpdatesPublisher $categoryPublisher
    ) {
        $this->categoryPublisher = $categoryPublisher;
    }

    /**
     * Reindex category permissions on category move event
     *
     * @param Category $category
     * @param Category $result
     * @return Category
     */
    public function afterMove(
        Category $category,
        Category $result
    ): Category {
        $categoryId = (string)$category->getId();
        $categoryIdsByStore = [];
        foreach ($category->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            $categoryIdsByStore[$storeId][] = [$categoryId];
            if ($category->getParentIds()) {
                $categoryIdsByStore[$storeId][] = $category->getParentIds();
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
