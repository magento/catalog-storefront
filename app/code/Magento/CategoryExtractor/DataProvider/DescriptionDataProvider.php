<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

use Magento\Catalog\Helper\Output as OutputHelper;

/**
 * Description data provider
 */
class DescriptionDataProvider implements DataProviderInterface
{
    /**
     * Description attribute code
     */
    private const ATTRIBUTE = 'description';

    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @var CategoriesProvider
     */
    private $categoriesProvider;

    /**
     * @param CategoriesProvider $categoriesProvider
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        CategoriesProvider $categoriesProvider,
        OutputHelper $outputHelper
    ) {
        $this->outputHelper = $outputHelper;
        $this->categoriesProvider = $categoriesProvider;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scope): array
    {
        $output = [];
        $attribute = !empty($attributes) ? key($attributes) : self::ATTRIBUTE;

        foreach ($this->categoriesProvider->getCategoriesByIds(
            $categoryIds,
            $scope['store'],
            [$attribute]
        ) as $category) {
            $description = $category->getDescription();
            $renderedValue = $this->outputHelper->categoryAttribute(null, $description, $attribute);
            $output[$category->getId()][$attribute] = $renderedValue;
        }

        return $output;
    }
}
