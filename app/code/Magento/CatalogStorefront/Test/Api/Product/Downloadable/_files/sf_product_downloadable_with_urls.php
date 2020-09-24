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
    'Magento/Downloadable/_files/product_downloadable_with_link_url_and_sample_url.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$downloadableProduct = $productRepository->get('downloadable-product');
$downloadableProduct->setWebsiteIds([1]);
$downloadableProduct->setLinksPurchasedSeparately(false);
$downloadableProduct->setLinksTitle('Product Links Title');
$productRepository->save($downloadableProduct);
