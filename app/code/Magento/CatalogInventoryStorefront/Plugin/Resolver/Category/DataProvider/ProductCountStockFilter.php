<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryStorefront\Plugin\Resolver\Category\DataProvider;

use Magento\CatalogCategory\DataProvider\Query\ProductsCountBuilder;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Add stock filter to product count query
 */
class ProductCountStockFilter
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Configuration $configuration
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Configuration $configuration
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configuration = $configuration;
    }

    /**
     * Add filter by stock status
     *
     * @param ProductsCountBuilder $subject
     * @param Select $select
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuery(ProductsCountBuilder $subject, Select $select)
    {
        if ($this->configuration->isShowOutOfStock()) {
            return $select;
        }
        $select
            ->joinInner(
                ['stock_status_index' => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
                'stock_status_index.product_id = cat_index.product_id',
                []
            )
            ->where('stock_status_index.stock_id = ?', Stock::DEFAULT_STOCK_ID)
            ->where('stock_status_index.stock_status = ?', StockStatusInterface::STATUS_IN_STOCK);

        return $select;
    }
}
