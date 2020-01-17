<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\CatalogCategory\Model\CategorySearch\CategoryFilter;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * @inheritdoc
 */
class ChildrenDataProvider implements DataProviderInterface
{
    /**
     * Children attributes
     */
    private const ATTRIBUTES = [
        'children' => []
    ];

    /**
     * @var DataProvider
     */
    private $generalDataProvider;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param DataProvider $generalDataProvider
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        DataProvider $generalDataProvider,
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory
    ) {
        $this->generalDataProvider = $generalDataProvider;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve category and category child items data
     *
     * Will build category tree based on requested attributes for provided category and it's child items
     *
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scope): array
    {
        $output = [];
        $attributes = !empty($attributes) ? $attributes : self::ATTRIBUTES;
        $attributeName = key($attributes);

        $categoryFilter = ['entity_id' => [$categoryIds]];
        $categoryCollection = $this->collectionFactory->create();
        $this->categoryFilter->applyFilters($categoryFilter, $categoryCollection, $scope);
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = $category->getId();
            $childCategories = $category->getChildrenCategories()->getAllIds();
            if (in_array($categoryId, $categoryIds)) {
                $output[$categoryId][$attributeName] = $this->generalDataProvider->fetch(
                    $childCategories,
                    $attributes[$attributeName],
                    $scope
                );
            }
        }

        return $output;
    }
}
