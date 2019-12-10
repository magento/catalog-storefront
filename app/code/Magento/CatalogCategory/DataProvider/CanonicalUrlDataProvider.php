<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\CatalogCategory\Model\CategorySearch\CategoryFilter;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Canonical URL data provider
 */
class CanonicalUrlDataProvider implements DataProviderInterface
{
    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CategoryHelper $categoryHelper
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryHelper $categoryHelper,
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $output = [];
        $attribute = key($attributes);

        $categoryFilter = ['entity_id' => [$categoryIds]];
        $categoryCollection = $this->collectionFactory->create();
        $this->categoryFilter->applyFilters($categoryFilter, $categoryCollection, $scopes);
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = $category->getId();
            $canonicalUrl = null;
            if ($this->categoryHelper->canUseCanonicalTag($scopes['store'])) {
                $baseUrl = $category->getUrlInstance()->getBaseUrl();
                $canonicalUrl = str_replace($baseUrl, '', $category->getUrl());
            }

            $output[$categoryId][$attribute] = $canonicalUrl;
        }

        return $output;
    }
}
