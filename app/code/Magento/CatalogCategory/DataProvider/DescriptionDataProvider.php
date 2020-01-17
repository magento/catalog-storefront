<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\CatalogCategory\Model\CategorySearch\CategoryFilter;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

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
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory,
        OutputHelper $outputHelper
    ) {
        $this->outputHelper = $outputHelper;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scope): array
    {
        $output = [];
        $attribute = !empty($attributes) ? key($attributes) : self::ATTRIBUTE;

        $categoryFilter = ['entity_id' => [$categoryIds]];
        $categoryCollection = $this->collectionFactory->create()->addAttributeToSelect($attribute);
        $this->categoryFilter->applyFilters($categoryFilter, $categoryCollection, $scope);
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $description = $category->getDescription();
            $renderedValue = $this->outputHelper->categoryAttribute(null, $description, $attribute);

            $output[$category->getId()][$attribute] = $renderedValue;
        }

        return $output;
    }
}
