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
     * @param CategoriesProvider $categoriesProvider
     */
    public function __construct(
        CategoriesProvider $categoriesProvider
    ) {
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

        foreach ($this->categoriesProvider->getCategoriesByIds($categoryIds, $scope['store']) as $category) {
            $categoryId = $category->getId();
            $childCategories = $category->getChildrenCategories()->getAllIds();
            $output[$categoryId][self::ATTRIBUTE] = $childCategories;
        }

        return $output;
    }
}
