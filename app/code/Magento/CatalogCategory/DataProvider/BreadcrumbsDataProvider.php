<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\CatalogProduct\DataProvider\Query\Category\CategoryAttributeQueryBuilder;
use Magento\Framework\App\ResourceConnection;

/**
 * Breadcrumbs data provider
 */
class BreadcrumbsDataProvider implements DataProviderInterface
{
    private const ATTRIBUTES = [
        'category_id',
        'category_name',
        'category_level',
        'category_url_key',
        'category_url_path'
    ];

    /**
     * @var CategoryAttributeQueryBuilder
     */
    private $categoryAttributeQueryBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DataProvider
     */
    private $generalDataProvider;

    /**
     * @param CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder
     * @param ResourceConnection $resourceConnection
     * @param DataProvider $generalDataProvider
     */
    public function __construct(
        CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder,
        ResourceConnection $resourceConnection,
        DataProvider $generalDataProvider
    ) {
        $this->categoryAttributeQueryBuilder = $categoryAttributeQueryBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->generalDataProvider = $generalDataProvider;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $categoryPaths = $this->generalDataProvider->fetch($categoryIds, ['path'], $scopes);
        $pathMap = [];
        $output = [];
        $attributes = $this->processAttributes($attributes);
        foreach ($categoryIds as $categoryId) {
            $categoryPath = $categoryPaths[$categoryId]['path'] ?? null;
            $entityIds = [];
            if (null !== $categoryPath) {
                $pathArray = \explode('/', $categoryPath);
                \array_pop($pathArray);
                $pathMap[$categoryId][$categoryPaths[$categoryId]['path']]  = \implode('/', $pathArray);
                $pathArray = \array_slice($pathArray, 2);
                $entityIds[] = $pathArray;
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $entityIds = \array_unique(\array_merge(...$entityIds));
                $categories = $this->getCategories($entityIds, (int)$scopes['store']);

                $childCategories =  $categories[$pathMap[$categoryId][$categoryPath]] ?? [];
                foreach ($childCategories as $child) {
                    $output[$categoryId]['breadcrumbs'][] = $this->getBreadcrumbs($child, $attributes);
                }
            }
        }

        return $output;
    }

    /**
     * Get categories for build breadcrumbs
     *
     * @param array $entityIds
     * @param int $storeId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getCategories(array $entityIds, int $storeId): array
    {
        $categories = [];
        if (!$entityIds) {
            return $categories;
        }

        $select = $this->categoryAttributeQueryBuilder->build(
            $entityIds,
            ['path', 'level', 'name', 'url_key', 'url_path'],
            $storeId
        );
        $entities = [];
        $statement = $this->resourceConnection->getConnection()->query($select);
        while ($row = $statement->fetch()) {
            $entities[$row['entity_id']]['category_' . $row['attribute_code']] = $row['value'];
            $entities[$row['entity_id']]['category_id'] = $row['entity_id'];
            $entities[$row['entity_id']]['category_path'] = $row['path'];
            $entities[$row['entity_id']]['category_level'] = $row['level'];
        }

        foreach ($entities as $entity) {
            $thread = [];
            $path = \explode('/', $entity['category_path']);
            $path = \array_slice($path, 2);
            foreach ($path as $node) {
                $thread[$node] = $entities[$node];
            }
            $categories[$entity['category_path']] = $thread;
        }

        return $categories;
    }

    /**
     * Get breadcrumbs by child category ID
     *
     * @param array $child
     * @param array $breadcrumbAttributes
     * @return array
     */
    private function getBreadcrumbs(array $child, array $breadcrumbAttributes): array
    {
        $result = [];
        foreach ($breadcrumbAttributes as $attribute) {
            $result[$attribute] = $child[$attribute] ?? null;
        }

        return $result;
    }

    /**
     * Process attributes.
     *
     * @param array $attributes
     * @return array
     */
    private function processAttributes(array $attributes): array
    {
        $attributes = empty($attributes)
            ? self::ATTRIBUTES
            : $attributes['breadcrumbs'];

        return $attributes;
    }
}
