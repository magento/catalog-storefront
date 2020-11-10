<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);


$eavConfig = $objectManager->get(Config::class);
$colorAttribute = $eavConfig->getAttribute('catalog_product', 'color_test');
$sizeAttribute = $eavConfig->getAttribute('catalog_product', 'size_test');
$eavConfig->clear();


/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);


if (!$sizeAttribute->getId()) {
    /** @var Attribute $sizeAttribute */
    $sizeAttribute = $objectManager->create(Attribute::class);
    $sizeAttribute->setData(
        [
            'attribute_code' => 'size_test',
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Size Test'],
            'backend_type' => 'int',
            'option' => [
                'value' => [
                    'small' => ['Small'],
                    'medium' => ['Medium'],
                    'large' => ['Large']
                ],
                'order' => ['small' => 0, 'medium' => 1, 'large' => 2],
            ]
        ]
    );
    $attributeRepository->save($sizeAttribute);
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $sizeAttribute->getId());
}

if (!$colorAttribute->getId()) {
    /** @var Attribute $colorAttribute */
    $colorAttribute = $objectManager->create(Attribute::class);
    $colorAttribute->setData(
        [
            'attribute_code' => 'color_test',
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Color Test'],
            'backend_type' => 'int',
            'option' => [
                'value' => [
                    'red' => ['Red'],
                    'blue' => ['Blue'],
                    'green' => ['Green'],
                ],
                'order' => ['red' => 0, 'blue' => 1, 'green' => 2],
            ],
        ]
    );
    $attributeRepository->save($colorAttribute);
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $colorAttribute->getId());
}

$eavConfig->clear();
