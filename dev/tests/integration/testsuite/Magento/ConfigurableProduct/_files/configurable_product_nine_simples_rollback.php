<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
//simple_' . $colorOption->getLabel() . '-' . $sizeOption->getLabel()
$productsToDelete = [
    'simple_0',
    'simple_1',
    'simple_2',
    'simple_3',
    'simple_4',
    'simple_5',
    'simple_6',
    'simple_7',
    'simple_8',
    'configurable'
];

foreach ($productsToDelete as $sku) {
    try {
        $product = $productRepository->get($sku, true);

        $stockStatus = $objectManager->create(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->load($product->getEntityId(), 'product_id');
        $stockStatus->delete();

        $productRepository->delete($product);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Product already removed
    }
}

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_two_attributes_three_options_rollback.php'
);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
