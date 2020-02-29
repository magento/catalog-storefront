<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventoryExtractor\Plugin\CollectProductsDataForUpdateAfterStockUpdate;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Override original plugin to run consumers during tests
 */
class StockStatusUpdate extends CollectProductsDataForUpdateAfterStockUpdate
{
    /**
     * @inheritDoc
     *
     * Ad-hoc solution. Force run consumers after inventory status update inside test-case
     *
     * @inheritDoc
     */
    public function afterUpdateStockItemBySku(
        StockRegistryInterface $subject,
        $result,
        string $productSku,
        StockItemInterface $stockItem
    ): int {
        $result = parent::afterUpdateStockItemBySku($subject, $result, $productSku, $stockItem);

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
