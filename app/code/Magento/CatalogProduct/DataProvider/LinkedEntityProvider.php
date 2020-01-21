<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider;

use Magento\CatalogProduct\Model\Storage\Client\Config\Category;
use Magento\CatalogProduct\Model\Storage\Client\Config\Product;

/**
 * Provide data for linked entities (categories, products, nested products...)
 */
class LinkedEntityProvider
{
    /**
     * @var CategoryDataProvider
     */
    private $categoryDataProvider;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var array
     */
    private $linkedEntityPath;

    /**
     * @param CategoryDataProvider $categoryDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param array $linkedEntityPath
     */
    public function __construct(
        CategoryDataProvider $categoryDataProvider,
        ProductDataProvider $productDataProvider,
        array $linkedEntityPath
    ) {
        $this->categoryDataProvider = $categoryDataProvider;
        $this->productDataProvider = $productDataProvider;
        $this->linkedEntityPath = $linkedEntityPath;
    }

    /**
     * Fetch product data from storage
     *
     * @param array $products
     * @param array $attributes
     * @param array $scopes
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function fetch(array $products, array $attributes, array $scopes): array
    {
        foreach ($this->linkedEntityPath as $entityType => $paths) {
            $entityIds = [];
            $entities = [];

            foreach ($paths as $path) {
                $path = \explode('.', $path);
                $entityIds[] = $this->getChildIds($products, $path);
            }
            $entityIds = $entityIds ? \array_merge(...$entityIds) : [];

            switch ($entityType) {
                case Product::ENTITY_NAME:
                    $entities = $entityIds ? $this->productDataProvider->fetch($entityIds, $attributes, $scopes) : [];
                    break;
                case Category::ENTITY_NAME:
                    $entities = $entityIds ? $this->categoryDataProvider->fetch($entityIds, $attributes, $scopes) : [];
                    break;
            }

            foreach ($paths as $path) {
                $path = \explode('.', $path);
                $this->updateParentEntities($products, $entities, $path);
            }
        }

        return $products;
    }

    /**
     * Retrieve child id from parents iterating over nested keys
     *
     * @param array $entities
     * @param array $nestedKeys
     * @return array
     */
    private function getChildIds(array $entities, $nestedKeys): array
    {
        $childIds = [];
        $nextKey = \array_shift($nestedKeys);
        foreach ($entities as $entity) {
            $nestedData = $entity[$nextKey] ?? null;
            if ($nestedData) {
                $childIds[] = empty($nestedKeys) ? (array)$nestedData : $this->getChildIds($nestedData, $nestedKeys);
            }
        }
        return !empty($childIds) ? \array_unique(\array_merge(...$childIds)) : [];
    }

    /**
     * Update parent entities with linked nested entities
     *
     * @param array $entities
     * @param array $childEntities
     * @param array $nestedKeys
     */
    private function updateParentEntities(array &$entities, array $childEntities, array $nestedKeys): void
    {
        if (!$childEntities) {
            return;
        }
        $nextKey = \array_shift($nestedKeys);
        foreach ($entities as &$entity) {
            $nestedData = &$entity[$nextKey] ?? null;
            if ($nestedData) {
                if (empty($nestedKeys)) {
                    if (\is_array($nestedData)) {
                        $entity[$nextKey] = \array_intersect_key(
                            $childEntities,
                            \array_combine($nestedData, $nestedData)
                        );
                    } else {
                        $entity[$nextKey] = $childEntities[$nestedData];
                    }
                } else {
                    $this->updateParentEntities($nestedData, $childEntities, $nestedKeys);
                }
            }
        }
    }
}
