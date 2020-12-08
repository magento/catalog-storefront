<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_different_labels_per_store_views.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
$rootCategoryId = $baseWebsite->getDefaultStore()->getRootCategoryId();

$firstAttribute = $productAttributeRepository->get('first_test_attribute');
$firstAttributeOptions = $firstAttribute->getOptions();
array_shift($firstAttributeOptions);

$secondAttribute = $productAttributeRepository->get('second_test_attribute');
$secondAttributeOptions = $secondAttribute->getOptions();
array_shift($secondAttributeOptions);

$thirdAttribute = $productAttributeRepository->get('third_test_attribute');
$thirdAttributeOptions = $thirdAttribute->getOptions();
array_shift($thirdAttributeOptions);


/** Create simple products */
$associatedProductIds = [];
$firstAttributeValues = [];
$secondAttributeValues = [];
$thirdAttributeValues = [];
$i = 0;
foreach ($firstAttributeOptions as $firstAttributeOption) {
    foreach ($secondAttributeOptions as $secondAttributeOption) {
        foreach ($thirdAttributeOptions as $thirdAttributeOption) {
            $childProduct = $productFactory->create();
            $childProduct->setTypeId(ProductType::TYPE_SIMPLE)
                ->setAttributeSetId($childProduct->getDefaultAttributeSetId())
                ->setWebsiteIds([$baseWebsite->getId()])
                ->setName('Simple ' . $i)
                ->setSku('simple_' . $i)
                ->setPrice(45)
                ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
                ->setStatus(Status::STATUS_ENABLED)
                ->setStockData([
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ])
                ->setCategoryIds([$rootCategoryId])
                ->setFirstTestAttribute($firstAttributeOption->getValue())
                ->setSecondTestAttribute($secondAttributeOption->getValue())
                ->setThirdTestAttribute($thirdAttributeOption->getValue());

            $childProduct = $productRepository->save($childProduct);

            $firstAttributeValues[] = [
                'label' => 'first test ' . $i,
                'attribute_id' => $firstAttribute->getId(),
                'value_index' => $firstAttributeOption->getValue(),
            ];
            $secondAttributeValues[] = [
                'label' => 'second test ' . $i,
                'attribute_id' => $secondAttribute->getId(),
                'value_index' => $secondAttributeOption->getValue(),
            ];
            $thirdAttributeValues[] = [
                'label' => 'third test ' . $i,
                'attribute_id' => $thirdAttribute->getId(),
                'value_index' => $thirdAttributeOption->getValue(),
            ];
            $associatedProductIds[] = $childProduct->getId();
            $i++;
        }
    }
}

/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);
$configurableAttributesData = [
    [
        'attribute_id' => $firstAttribute->getId(),
        'code' => $firstAttribute->getAttributeCode(),
        'label' => $firstAttribute->getStoreLabel(),
        'position' => '0',
        'values' => $firstAttributeValues,
    ],
    [
        'attribute_id' => $secondAttribute->getId(),
        'code' => $secondAttribute->getAttributeCode(),
        'label' => $secondAttribute->getStoreLabel(),
        'position' => '1',
        'values' => $secondAttributeValues,
    ],
    [
        'attribute_id' => $thirdAttribute->getId(),
        'code' => $secondAttribute->getAttributeCode(),
        'label' => $secondAttribute->getStoreLabel(),
        'position' => '2',
        'values' => $thirdAttributeValues,
    ]
];

$configurableOptions = $optionsFactory->create($configurableAttributesData);

$product = $productFactory->create();
/** @var ProductExtensionFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ProductExtensionFactory::class);
$extensionConfigurableAttributes = $product->getExtensionAttributes() ?: $extensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([$rootCategoryId])
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);
