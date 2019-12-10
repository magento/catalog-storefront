<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\Category;

use Magento\CatalogCategoryApi\Api\CategorySearchInterface;
use Magento\CatalogCategoryApi\Api\Data\CategorySearchCriteriaInterfaceFactory;
use Magento\CatalogProduct\DataProvider\Query\Product\ProductCategoriesQuery;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogProduct\DataProvider\DataProviderInterface;

/**
 * Product categories data provider, used for GraphQL resolver processing.
 */
class ProductCategoriesDataProvider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductCategoriesQuery
     */
    private $productCategoriesQuery;

    /**
     * @var array
     */
    private static $requiredCategoryAttributes = [
        'entity_id',
        'path',
        'level',
    ];
    /**
     * @var CategorySearchInterface
     */
    private $categorySearch;

    /**
     * @var CategorySearchCriteriaInterfaceFactory
     */
    private $categorySearchCriteriaFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductCategoriesQuery $productCategoriesQuery
     * @param CategorySearchInterface $categorySearch
     * @param CategorySearchCriteriaInterfaceFactory $categorySearchCriteriaFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductCategoriesQuery $productCategoriesQuery,
        CategorySearchInterface $categorySearch,
        CategorySearchCriteriaInterfaceFactory $categorySearchCriteriaFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productCategoriesQuery = $productCategoriesQuery;
        $this->categorySearch = $categorySearch;
        $this->categorySearchCriteriaFactory = $categorySearchCriteriaFactory;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        // get categories of products
        $categoryProductsSelect = $this->productCategoriesQuery->getQuery($productIds, $scopes);
        $connection = $this->resourceConnection->getConnection();
        $categoryProducts = $connection->fetchAll($categoryProductsSelect);

        // get categories and product -> categories map
        $productCategories = [];
        $categoryIds = [];

        foreach ($categoryProducts as $item) {
            $categoryIds[$item['category_id']] = $item['category_id'];
            $productCategories[$item['product_id']][] = $item['category_id'];
        }

        // TODO: return only categories ids
        // get categories attributes
        $attributeCodes = \array_merge($attributes['categories'], self::$requiredCategoryAttributes);

        $requests = $this->categorySearchCriteriaFactory->create(
            [
                'filters' => ['ids' => ['in' => $categoryIds]],
                'scopes' => $scopes,
                'attributes' => $attributeCodes
            ]
        );
        $categories = $this->categorySearch->search([$requests])[0]->getCategories();

        // format output
        $output = [];

        foreach ($productIds as $productId) {
            $output[$productId] = [];
            $output[$productId]['categories'] = [];
            if (isset($productCategories[$productId])) {
                foreach ($productCategories[$productId] as $categoryId) {
                    if (!empty($categories[$categoryId])) {
                        $output[$productId]['categories'][] = $categories[$categoryId];
                    }
                }
            }
        }

        return $output;
    }
}
