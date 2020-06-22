<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Query\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver as TableResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\Dimension;

/**
 * Fetch categories and products by given criteria.
 */
class ProductCategoriesQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TableResolver $tableResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TableResolver $tableResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableResolver = $tableResolver;
    }

    /**
     * Form and return query to get categories for given products
     *
     * @param array $productIds
     * @param array $scopes
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getQuery(
        array $productIds,
        array $scopes
    ): Select {
        $storeId = (int)$scopes['store'];
        $connection = $this->resourceConnection->getConnection();
        $categoryProductTable = $this->getCatalogCategoryProductTableName($storeId);
        $storeTable = $this->resourceConnection->getTableName('store');
        $storeGroupTable = $this->resourceConnection->getTableName('store_group');

        $select = $connection->select()
            ->from(
                ['cat_index' => $categoryProductTable],
                ['category_id', 'product_id']
            )
            ->joinInner(['store' => $storeTable], $connection->quoteInto('store.store_id = ?', $storeId), [])
            ->joinInner(
                ['store_group' => $storeGroupTable],
                'store.group_id = store_group.group_id AND cat_index.category_id != store_group.root_category_id',
                []
            )
            ->where('product_id IN (?)', $productIds)
            ->order('category_id DESC');

        return $select;
    }

    /**
     * Returns name of catalog_category_product_index table based on currently used dimension.
     *
     * @param int $storeId
     * @return string
     */
    private function getCatalogCategoryProductTableName(int $storeId)
    {
        $catalogCategoryProductDimension = new Dimension(
            \Magento\Store\Model\Store::ENTITY,
            $storeId
        );

        $tableName = $this->tableResolver->resolve(
            \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction::MAIN_INDEX_TABLE,
            [
                $catalogCategoryProductDimension
            ]
        );

        return $tableName;
    }
}
