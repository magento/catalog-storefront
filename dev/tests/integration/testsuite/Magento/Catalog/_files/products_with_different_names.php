<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$products = [
    ['name' => 'Apple', 'price'=> 1],
    ['name' => 'AppleOne', 'price' => 2],
    ['name' => 'Apple One', 'price' => 3],
    ['name' => 'AppleApple', 'price' => 4],
    ['name' => 'Apple Apple', 'price' => 5],
    ['name' => 'Appl', 'price' => 6],
    ['name' => 'One Apple One', 'price' => 7],
    ['name' => 'OneApple', 'price' => 8],
    ['name' => 'One Apple', 'price' => 9],
    ['name' => 'AApple', 'price' => 10],
    ['name' => 'AApplee', 'price' => 11],
    ['name' => 'Applee', 'price' => 12],
    ['name' => 'Orange', 'price' => 13],
];

$productTemplate = [
    'type' => 'simple',
    'sku' => 'search_product_name_',
    'status' => Status::STATUS_ENABLED,
    'visibility' => Visibility::VISIBILITY_BOTH,
    'attribute_set' => 4,
    'website_ids' => [1],
    'category_ids' => [1],
];

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
foreach ($products as $product) {
    $sku = $productTemplate['sku'] . $product['name'];

    /** @var $product Product */
    $newProduct = $objectManager->create(Product::class);
    $newProduct
        ->setTypeId($productTemplate['type'])
        ->setAttributeSetId($productTemplate['attribute_set'])
        ->setWebsiteIds($productTemplate['website_ids'])
        ->setName($product['name'])
        ->setSku($sku)
        ->setUrlKey(microtime(false))
        ->setPrice($product['price'])
        ->setVisibility($productTemplate['visibility'])
        ->setStatus($productTemplate['status'])
        ->setStockData(['use_config_manage_stock' => 0]);
    $productRepository->save($newProduct);

    $categoryLinkManagement->assignProductToCategories($sku, $productTemplate['category_ids']);
}
