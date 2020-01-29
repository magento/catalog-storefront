<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate::class => \Magento\StorefrontTestFixer\CategoryAfterSave::class,
    \Magento\CatalogStorefrontConnector\Plugin\CollectProductsDataOnSave::class => \Magento\StorefrontTestFixer\ProductAfterSave::class
];
