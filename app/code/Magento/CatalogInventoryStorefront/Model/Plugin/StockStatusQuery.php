<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventoryStorefront\Model\Plugin;

use Magento\ConfigurableProductExtractor\DataProvider\Query\ProductVariantsBuilder;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Modify search query according to stock configuration
 */
class StockStatusQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Configuration
     */
    private $stockConfiguration;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Configuration $stockConfiguration
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Configuration $stockConfiguration
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Add join for IN_STOCK products only if the "Is Show Out Of Stock" option disabled
     *
     * @param ProductVariantsBuilder $subject
     * @param Select $result
     * @param array $parentProducts
     * @param array $scopes
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(
        ProductVariantsBuilder $subject,
        Select $result,
        array $parentProducts,
        array $scopes
    ): Select {
        if (false === $this->stockConfiguration->isShowOutOfStock($scopes['store'])) {
            $result->joinInner(
                ['stock_status' => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
                'stock_status.product_id = product_link.product_id AND stock_status.stock_status = '
                    . Stock::STOCK_IN_STOCK,
                []
            );
        }

        return $result;
    }
}
