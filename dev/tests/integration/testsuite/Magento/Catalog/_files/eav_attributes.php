<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

$eavAttributes = [
    'text' => [
        'attribute_code' => 'text_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'text',
        'is_used_for_promo_rules' => 1,
        'backend_type' => 'text',
    ],
    'multiselect' => [
        'attribute_code' => 'multiselect_attribute',
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
        'frontend_label' => ['Test Configurable'],
        'backend_type' => 'int',
        'option' => [
            'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
    ],
    'textarea' => [
        'attribute_code' => 'text_area_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'textarea',
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
        'frontend_label' => ['Text Attribute'],
        'backend_type' => 'text',
    ],
    'texteditor' => [
        'attribute_code' => 'text_editor_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'texteditor',
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
        'frontend_label' => ['Text editor attribute'],
        'backend_type' => 'text',
    ],
    'datetime' => [
        'attribute_code' => 'datetime_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'datetime',
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
        'used_in_product_listing' => 1,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Date Time Attribute'],
        'backend_type' => 'datetime',
    ],
    'date' => [
        'attribute_code' => 'date_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'date',
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
        'used_in_product_listing' => 1,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Date Attribute'],
        'backend_type' => 'datetime',
    ],
    'boolean' => [
        'attribute_code'                => 'boolean_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global'                     => 0,
        'is_user_defined'               => 1,
        'frontend_input'                => 'boolean',
        'is_unique'                     => 0,
        'is_required'                   => 0,
        'is_searchable'                 => 1,
        'is_visible_in_advanced_search' => 1,
        'is_comparable'                 => 0,
        'is_filterable'                 => 1,
        'is_filterable_in_search'       => 1,
        'is_used_for_promo_rules'       => 0,
        'is_html_allowed_on_front'      => 1,
        'is_visible_on_front'           => 1,
        'used_in_product_listing'       => 1,
        'used_for_sort_by'              => 0,
        'frontend_label'                => ['Boolean Attribute'],
        'backend_type'                  => 'int',
        'source_model'                  => Boolean::class
    ],

    'price' => [
        'attribute_code' => 'price_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'price',
        'is_used_for_promo_rules' => 1,
        'backend_type' => 'decimal',
    ],
    'media_image' => [
        'attribute_code' => 'image_attribute',
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'media_image',
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
        'used_in_product_listing' => 1,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Image Attribute'],
        'backend_type' => 'varchar',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
    ],
    'weee' => [
        'attribute_code' => 'weee_attribute',
        'backend_model' => 'Magento\Weee\Model\Attribute\Backend\Weee\Tax',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_static' => 1,
        'frontend_input' => 'weee',
        'frontend_label' => 'fixed product tax',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
    ],
//    this has same format as multiselect attribute
//    'dropdown' => [
//        'attribute_code'                => 'dropdown_attribute',
//        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
//        'is_global'                     => 0,
//        'is_user_defined'               => 1,
//        'frontend_input'                => 'select',
//        'is_unique'                     => 0,
//        'is_required'                   => 0,
//        'is_searchable'                 => 0,
//        'is_visible_in_advanced_search' => 0,
//        'is_comparable'                 => 0,
//        'is_filterable'                 => 0,
//        'is_filterable_in_search'       => 0,
//        'is_used_for_promo_rules'       => 0,
//        'is_html_allowed_on_front'      => 1,
//        'is_visible_on_front'           => 1,
//        'used_in_product_listing'       => 1,
//        'used_for_sort_by'              => 0,
//        'frontend_label'                => ['Drop-Down Attribute'],
//        'backend_type'                  => 'int',
//        'option'                        => [
//            'value' => [
//                'option_1' => ['Option 1'],
//                'option_2' => ['Option 2'],
//                'option_3' => ['Option 3'],
//            ],
//            'order' => [
//                'option_1' => 1,
//                'option_2' => 2,
//                'option_3' => 3,
//            ],
//        ],
//    ]
];

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
foreach ($eavAttributes as $key => $eavAttribute) {
    $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
    );

    $attribute->setData(
        $eavAttribute
    );
    $attribute->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
