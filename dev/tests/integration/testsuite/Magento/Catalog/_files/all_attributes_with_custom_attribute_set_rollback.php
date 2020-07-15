<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);

/** @var \Magento\Eav\Api\AttributeRepositoryInterface $eavRepository */
$eavRepository = $objectManager->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);

$eavAttributes = [
        'text_attribute',
        'multiselect_attribute',
        'text_area_attribute',
        'text_editor_attribute',
        'boolean_attribute',
//        'date_attribute',
//        'datetime_attribute',
        'image_attribute',
//        'weee_attribute',
        'price_attribute'
];

foreach ($eavAttributes as $attribute) {
    if($eavRepository->get($installer->getEntityTypeId('catalog_product'), $attribute)) {
        $attribute = $eavRepository->get($installer->getEntityTypeId('catalog_product'), $attribute);
        if($attribute->getAttributeId()) {
            $eavRepository->delete($attribute);
        }
    }
}

//delete attribute set
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('new_attribute_set_storefront', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
