<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Category;

use Magento\CatalogExtractor\DataProvider\Query\Product\ProductCategoriesQuery;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;

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
     * @param ResourceConnection $resourceConnection
     * @param ProductCategoriesQuery $productCategoriesQuery
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductCategoriesQuery $productCategoriesQuery
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productCategoriesQuery = $productCategoriesQuery;
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

        foreach ($categoryProducts as $item) {
            $productCategories[$item['product_id']]['categories'][] = $item['category_id'];
        }
        return $productCategories;
    }
}
