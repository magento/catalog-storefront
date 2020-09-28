<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_image.php');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

$galleryEntries = [];
foreach ($product->getMediaGalleryEntries() as $key => $entry) {
    $galleryEntries[$key] = $entry->setDisabled(true);
}

$productRepository->save($product->setMediaGalleryEntries($galleryEntries));
