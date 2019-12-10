<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryStorefront\DataProvider\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Build Select for fetch product stock status
 */
class StockStatusBuilder
{
    /**
     * Stock scope id
     * \Magento\CatalogInventory\Api\StockConfigurationInterface::getDefaultScopeId
     */
    private const SCOPE_ID = 0;

    /**
     * Default stock id
     * \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID
     */
    private const DEFAULT_STOCK_ID = 1;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Form and return query to get product stock status
     *
     * @param int[] $productIds
     * @return Select
     */
    public function build(array $productIds): Select
    {
        return $this->resourceConnection->getConnection()
            ->select()
            ->from(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                ['product_id', 'stock_status']
            )
            ->where('product_id IN (?)', $productIds)
            ->where('stock_id = ?', self::DEFAULT_STOCK_ID)
            ->where('website_id = ?', self::SCOPE_ID);
    }
}
