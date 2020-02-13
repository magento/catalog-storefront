<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

/**
 * Plugin for collect category data during saving process
 */
class CollectCategoriesDataOnSave
{
    /**
     * @var CategoryUpdatesPublisher
     */
    private $categoryPublisher;

    /**
     * @var ProductUpdatesPublisher
     */
    private $productPublisher;

    /**
     * @param CategoryUpdatesPublisher $categoryPublisher
     * @param ProductUpdatesPublisher $productPublisher
     */
    public function __construct(
        CategoryUpdatesPublisher $categoryPublisher,
        ProductUpdatesPublisher $productPublisher
    ) {
        $this->categoryPublisher = $categoryPublisher;
        $this->productPublisher = $productPublisher;
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
        $categoryId = (string)$category->getId();
        foreach ($category->getStoreIds() as $storeId) {
            $storeId = (int)$storeId;
            if ($storeId === Store::DEFAULT_STORE_ID) {
                continue ;
            }
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $categoryIds = array_merge([$categoryId], $category->getParentIds());
            $this->categoryPublisher->publish($categoryIds, $storeId);
            if (!empty($category->getChangedProductIds())) {
                $productIds = $category->getChangedProductIds();
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $this->productPublisher->publish($productIds, $storeId);
            }
        }

        return $result;
    }
}
