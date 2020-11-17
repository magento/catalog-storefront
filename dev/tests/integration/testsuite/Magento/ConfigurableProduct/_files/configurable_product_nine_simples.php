<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_two_attributes_three_options.php'
);

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager
    ->get(ProductRepositoryInterface::class);
/** @var AttributeRepository $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepository::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();

/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

/** Get attributes */
$colorAttribute = $attributeRepository->get('catalog_product', 'color_test');
$sizeAttribute = $attributeRepository->get('catalog_product', 'size_test');
$colorAttributeOptions = $colorAttribute->getOptions();
$sizeAttributeOptions = $sizeAttribute->getOptions();

//remove the first option which is empty
array_shift($sizeAttributeOptions);
array_shift($colorAttributeOptions);

/** Create simple products */
$associatedProductIds = [];
$colorAttributeValues = [];
$sizeAttributeValues = [];
$i = 0;
foreach ($colorAttributeOptions as $colorOption) {
    foreach ($sizeAttributeOptions as $sizeOption) {
        /** @var $childProduct Product */
        $childProduct = $objectManager->create(Product::class);
        $childProduct->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([$defaultWebsiteId])
            ->setName('Simple ' . $colorOption->getLabel() . '-' . $sizeOption->getLabel())
            ->setSku('simple_' . $i)
            ->setPrice(45)
            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
            ->setColorTest($colorOption->getValue())
            ->setSizeTest($sizeOption->getValue());

        $childProduct = $productRepository->save($childProduct);

        $colorAttributeValues[] = [
            'label' => 'color test ' . $i,
            'attribute_id' => $colorAttribute->getId(),
            'value_index' => $colorOption->getValue(),
        ];
        $sizeAttributeValues[] = [
            'label' => 'size test ' . $i,
            'attribute_id' => $sizeAttribute->getId(),
            'value_index' => $sizeOption->getValue(),
        ];
        $associatedProductIds[] = $childProduct->getId();
        $i++;
    }
}

/** Create configurable product */
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);
$configurableAttributesData = [
    [
        'attribute_id' => $colorAttribute->getId(),
        'code' => $colorAttribute->getAttributeCode(),
        'label' => $colorAttribute->getStoreLabel(),
        'position' => '0',
        'values' => $colorAttributeValues,
    ],
    [
        'attribute_id' => $sizeAttribute->getId(),
        'code' => $sizeAttribute->getAttributeCode(),
        'label' => $sizeAttribute->getStoreLabel(),
        'position' => '1',
        'values' => $sizeAttributeValues,
    ],
];

$configurableProduct = $objectManager->create(Product::class);
$configurableProduct->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

$configurableOptions = $optionsFactory->create($configurableAttributesData);
$extensionConfigurableAttributes = $configurableProduct->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);

$productRepository->save($configurableProduct);
