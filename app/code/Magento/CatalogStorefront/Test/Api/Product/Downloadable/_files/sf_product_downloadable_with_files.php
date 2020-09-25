<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture(
    'Magento/Downloadable/_files/product_downloadable_with_files.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$downloadableProduct = $productRepository->get('downloadable-product');
$downloadableProduct->setLinksTitle('Product Links Title');
$downloadableProduct->setLinksPurchasedSeparately(true);
$productRepository->save($downloadableProduct);
