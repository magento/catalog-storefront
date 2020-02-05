<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogStorefrontConnector\Plugin\CollectProductsDataForUpdateAfterStockUpdate;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin for collect product data during update of stock status
 */
class StockStatusUpdate extends CollectProductsDataForUpdateAfterStockUpdate
{
    /**
     * @inheritDoc
     *
     * Ad-hoc solution. Force run consumers after inventory status update inside test-case
     *
     * @param StockRegistryInterface $subject
     * @param void $result
     * @param string $productSku
     * @param StockItemInterface $stockItem
     */
    public function afterUpdateStockItemBySku(
        StockRegistryInterface $subject,
        $result,
        string $productSku,
        StockItemInterface $stockItem
    ): void {
        $result = parent::afterUpdateStockItemBySku($subject, $result, $productSku, $stockItem);

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);
    }
}
