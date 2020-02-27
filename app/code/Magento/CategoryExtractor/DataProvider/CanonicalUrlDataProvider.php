<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

use Magento\Catalog\Helper\Category as CategoryHelper;

/**
 * Canonical URL data provider
 */
class CanonicalUrlDataProvider implements DataProviderInterface
{
    /**
     * Canonical url attribute code
     */
    private const ATTRIBUTE = 'canonical_url';

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var CategoriesProvider
     */
    private $categoriesProvider;

    /**
     * @param CategoryHelper $categoryHelper
     * @param CategoriesProvider $categoriesProvider
     */
    public function __construct(
        CategoryHelper $categoryHelper,
        CategoriesProvider $categoriesProvider
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->categoriesProvider = $categoriesProvider;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $output = [];
        $attribute = !empty($attributes) ? key($attributes) : self::ATTRIBUTE;

        foreach ($this->categoriesProvider->getCategoriesByIds($categoryIds, $scopes['store']) as $category) {
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
