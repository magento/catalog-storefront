<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

/**
 * @inheritdoc
 */
class ChildrenDataProvider implements DataProviderInterface
{
    /**
     * Children attribute
     */
    private const ATTRIBUTE = 'children';

    /**
     * @var CategoriesProvider
     */
    private $categoriesProvider;

    /**
     * @var DataProvider
     */
    private $generalDataProvider;

    /**
     * @param CategoriesProvider $categoriesProvider
     * @param DataProvider $generalDataProvider
     */
    public function __construct(
        CategoriesProvider $categoriesProvider,
        DataProvider $generalDataProvider
    ) {
        $this->categoriesProvider = $categoriesProvider;
        $this->generalDataProvider = $generalDataProvider;
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

        $attributeName = key($attributes);

        foreach ($this->categoriesProvider->getCategoriesByIds($categoryIds, (int)$scope['store']) as $category) {
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
