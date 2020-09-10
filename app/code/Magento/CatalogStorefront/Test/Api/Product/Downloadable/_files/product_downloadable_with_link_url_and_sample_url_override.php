<?php
//TODO:: Revert data fixture once we can find and fix the issue with the original data fixture.
// https://github.com/magento/catalog-storefront/issues/302.
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Api\DomainManagerInterface;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var DomainManagerInterface $domainManager */
$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->addDomains(
    [
        'example.com',
        'www.example.com',
        'www.sample.example.com'
    ]
);

/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$sampleFactory = $objectManager->create(\Magento\Downloadable\Api\Data\SampleInterfaceFactory::class);
$linkFactory = $objectManager->create(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);

$downloadableData = [
    'sample' => [
        [
            'is_delete' => 0,
            'sample_id' => 0,
            'title' => 'Downloadable Product Sample Title',
            'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
            'file' => null,
            'sample_url' => 'http://example.com/downloadable.txt',
            'sort_order' => '0',
        ],
    ],
];
$product->setTypeId(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Downloadable Product'
)->setSku(
    'downloadable-product'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
);

$extension = $product->getExtensionAttributes();
$links = [];
$linkData = [
    'product_id' => 1,
    'sort_order' => '0',
    'title' => 'Downloadable Product Link',
    'sample' => [
        'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
        'url' => 'http://example.com/downloadable.txt',
    ],
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => 'http://example.com/downloadable.txt',
    'is_delete' => 0,
    'number_of_downloads' => 15,
    'price' => 15.00,
];
$link = $linkFactory->create(['data' => $linkData]);
$link->setId(null);
$link->setSampleType($linkData['sample']['type']);

$link->setSampleUrl($linkData['sample']['url']);
$link->setLinkType($linkData['type']);
$link->setStoreId($product->getStoreId());
$link->setWebsiteId($product->getStore()->getWebsiteId());
$link->setProductWebsiteIds($product->getWebsiteIds());
if (!$link->getSortOrder()) {
    $link->setSortOrder(1);
}
if (null === $link->getPrice()) {
    $link->setPrice(0);
}
if ($link->getIsUnlimited()) {
    $link->setNumberOfDownloads(0);
}
$links[] = $link;

$extension->setDownloadableProductLinks($links);

if (isset($downloadableData['sample']) && is_array($downloadableData['sample'])) {
    $samples = [];
    foreach ($downloadableData['sample'] as $sampleData) {
        if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
            continue;
        } else {
            unset($sampleData['sample_id']);
            /**
             * @var \Magento\Downloadable\Api\Data\SampleInterface $sample
             */
            $sample = $sampleFactory->create(['data' => $sampleData]);
            $sample->setId(null);
            $sample->setStoreId($product->getStoreId());
            $sample->setSampleType($sampleData['type']);
            $sample->setSampleUrl($sampleData['sample_url']);
            $sample->setSortOrder($sampleData['sort_order']);
            $samples[] = $sample;
        }
    }
    $extension->setDownloadableProductSamples($samples);
}
$product->setExtensionAttributes($extension);

if ($product->getLinksPurchasedSeparately()) {
    $product->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
} else {
    $product->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
}
$product->save();

$stockRegistry = $objectManager->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
$stockItem = $stockRegistry->getStockItem($product->getId());
$stockItem->setUseConfigManageStock(true);
$stockItem->setQty(100);
$stockItem->setIsInStock(true);
$stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
