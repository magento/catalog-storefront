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
 * Hydrate entities with linked entities defined map $linkedEntityPath
 */
class LinkedEntityHydrator
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
     * Hydrate $products with linked entities defined $this->linkedEntityPath map
     *
     * @param array $products
     * @param array $attributes
     * @param array $scopes
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function hydrate(array $products, array $attributes, array $scopes): array
    {
        foreach ($this->linkedEntityPath as $entityType => $paths) {
            $entityIds = [];
            $entities = [];

            $linkedEntityAttributes = [];
            foreach ($paths as $path) {
                $path = \explode('.', $path);
                $entityIds[] = $this->getChildIds($products, $path);
                $linkedEntityAttributes[] = $this->getAttributes($attributes, $path);
            }
            $entityIds = $entityIds ? \array_unique(\array_merge(...$entityIds)) : [];
            $linkedEntityAttributes = $linkedEntityAttributes ? \array_merge(...$linkedEntityAttributes) : [];

            switch ($entityType) {
                case Product::ENTITY_NAME:
                    $entities = $entityIds
                        ? $this->productDataProvider->fetch($entityIds, $linkedEntityAttributes, $scopes)
                        : [];
                    break;
                case Category::ENTITY_NAME:
                    $entities = $entityIds
                        ? $this->categoryDataProvider->fetch($entityIds, $linkedEntityAttributes, $scopes)
                        : [];
                    break;
            }

            foreach ($paths as $path) {
                $path = \explode('.', $path);
                $this->trimEntityType($path);
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
    private function getChildIds(array $entities, array $nestedKeys): array
    {
        $this->trimEntityType($nestedKeys);
        return $this->getNestedIdsByKeyPath($entities, $nestedKeys);
    }

    /**
     * Retrieve nested data from multi dimensional array iterating over nested keys
     *
     * @param array $entities
     * @param array $nestedKeys
     * @return array
     */
    private function getNestedIdsByKeyPath(array $entities, array $nestedKeys): array
    {
        $childIds = [];
        $nextKey = \array_shift($nestedKeys);
        foreach ($entities as $entity) {
            $nestedData = $entity[$nextKey] ?? null;
            if ($nestedData) {
                $childIds[] = empty($nestedKeys)
                    ? (array)$nestedData
                    : $this->getNestedIdsByKeyPath($nestedData, $nestedKeys);
            }
        }
        return !empty($childIds) ? \array_merge(...$childIds) : [];
    }

    /**
     * Retrieve nested attributes iterating over nested keys
     *
     * @param array $attributes
     * @param array $nestedKeys
     * @return array
     */
    private function getNestedAttributes(array $attributes, array $nestedKeys): array
    {
        $nestedAttributes = [];
        $nextKey = \array_shift($nestedKeys);
        $nestedData = $attributes[$nextKey] ?? null;
        if ($nestedData) {
            $nestedAttributes = empty($nestedKeys) ? $nestedData : $this->getNestedAttributes($nestedData, $nestedKeys);
        }
        return $nestedAttributes;
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

    /**
     * Get attributes for nested entity by searching through $path in $attributes
     *
     * @param array $attributes
     * @param array $path
     * @return array
     */
    private function getAttributes(array $attributes, array $path): array
    {
        // convert "ProductType::items" to "ProductType.items"
        $path[0] = \str_replace(':', '.', $path[0]);
        return $this->getNestedAttributes($attributes, $path);
    }

    /**
     * Trim entity type from path: convert "ProductType::items" to "items" for the first element in $nestedKeys
     *
     * @param array $nestedKeys
     * @return void
     */
    private function trimEntityType(array &$nestedKeys): void
    {
        if (false !== ($typeDelimiterPosition = \strpos($nestedKeys[0], ':'))) {
            $nestedKeys[0] = \substr($nestedKeys[0], $typeDelimiterPosition + 1);
        }
    }
}
