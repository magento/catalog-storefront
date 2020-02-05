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
     * @var CategoriesProvider
     */
    private $categoriesProvider;

    /**
     * @param DataProvider $generalDataProvider
     * @param CategoriesProvider $categoriesProvider
     */
    public function __construct(
        DataProvider $generalDataProvider,
        CategoriesProvider $categoriesProvider
    ) {
        $this->generalDataProvider = $generalDataProvider;
        $this->categoriesProvider = $categoriesProvider;
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

        foreach ($this->categoriesProvider->getCategoriesByIds($categoryIds) as $category) {
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
