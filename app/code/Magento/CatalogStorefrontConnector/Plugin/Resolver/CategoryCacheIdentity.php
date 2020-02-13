<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin\Resolver;

use \Magento\CatalogGraphQl\Model\Resolver\Product\Identity;

/**
 * Add category cache identities for case, when categories already present in resolved value of Product resolver
 */
class CategoryCacheIdentity
{
    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Category\CategoriesIdentity
     */
    private $categoriesIdentity;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Category\CategoriesIdentity $categoriesIdentity
     */
    public function __construct(\Magento\CatalogGraphQl\Model\Resolver\Category\CategoriesIdentity $categoriesIdentity)
    {
        $this->categoriesIdentity = $categoriesIdentity;
    }

    /**
     * Add category identities
     *
     * @param Identity $subject
     * @param array $tags
     * @param array $resolvedData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIdentities(Identity $subject, array $tags, array $resolvedData): array
    {
        $items = $resolvedData['items'] ?? [];
        $categories = [];
        foreach ($items as $item) {
            $categories[] = $item['categories'] ?? [];
        }
        $categories = \array_merge(...$categories);

        return empty($categories) ? $tags : \array_merge($tags, $this->categoriesIdentity->getIdentities($categories));
    }
}
