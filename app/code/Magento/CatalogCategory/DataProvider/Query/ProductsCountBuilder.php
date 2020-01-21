<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\Store;

/**
 * Build Select for product count query
 */
class ProductsCountBuilder
{
    /**
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param Visibility $catalogProductVisibility
     * @param ResourceConnection $resourceConnection
     * @param IndexScopeResolverInterface $scopeResolver
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        ResourceConnection $resourceConnection,
        IndexScopeResolverInterface $scopeResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->resourceConnection = $resourceConnection;
        $this->scopeResolver = $scopeResolver;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * Get query for retrieve count of products per category
     *
     * @param array $categoryIds
     * @param int $storeId
     * @return Select
     */
    public function getQuery(array $categoryIds, int $storeId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $storeDimension = $this->dimensionFactory->create(
            Store::ENTITY,
            (string)$storeId
        );
        $categoryTable = $this->scopeResolver->resolve('catalog_category_product_index', [$storeDimension]);

        return $connection->select()
            ->from(
                ['cat_index' => $categoryTable],
                ['category_id' => 'cat_index.category_id', 'count' => 'count(cat_index.product_id)']
            )
            ->where('cat_index.visibility in (?)', $this->catalogProductVisibility->getVisibleInSiteIds())
            ->where('cat_index.category_id in (?)', \array_map('\intval', $categoryIds))
            ->group('cat_index.category_id');
    }
}
