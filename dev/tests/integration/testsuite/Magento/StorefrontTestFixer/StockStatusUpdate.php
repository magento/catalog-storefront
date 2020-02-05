<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

/**
 * Plugin for collect category data during saving process
 *
 * Due to changes in DI (added afterUpdateStockItemBySku() plugins) for store front application
 * we added empty plugin classes to keep plugin initialization chain
 */
class StockStatusUpdate extends \Magento\CatalogStorefrontConnector\Plugin\CollectProductsDataForUpdateAfterStockUpdate
{

}
