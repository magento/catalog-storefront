<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\Visibility;

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
     * @param Visibility $catalogProductVisibility
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        ResourceConnection $resourceConnection
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get query for retrieve count of products per category
     *
     * @param array $categoryIds
     * @return \Magento\Framework\DB\Select
     */
    public function getQuery(array $categoryIds): \Magento\Framework\DB\Select
    {
        $connection = $this->resourceConnection->getConnection();
        $categoryTable = $this->resourceConnection->getTableName('catalog_category_product_index');

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
