<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataOnSave::class
        => \Magento\StorefrontTestFixer\CategoryAfterSave::class,
    \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate::class
        => \Magento\StorefrontTestFixer\CategoryOnUpdate::class,
    \Magento\CatalogInventoryExtractor\Plugin\UpdateCategoriesOnConfigurationChange::class
        => \Magento\StorefrontTestFixer\CategoriesOnConfigurationChange::class,
    \Magento\CatalogStorefrontConnector\Plugin\CollectProductsDataOnSave::class
        => \Magento\StorefrontTestFixer\ProductAfterSave::class,
    \Magento\CatalogInventoryExtractor\Plugin\CollectProductsDataForUpdateAfterStockUpdate::class
        => \Magento\StorefrontTestFixer\StockStatusUpdate::class,
    \Magento\CatalogStorefrontConnector\Plugin\CategoryOnDelete::class
        => \Magento\StorefrontTestFixer\CategoryOnDelete::class,
    \Magento\CatalogStorefrontConnector\Model\Sync\SyncStorageOnStoreSave::class
        => \Magento\StorefrontTestFixer\StorageOnStoreSaveFixer::class
];
