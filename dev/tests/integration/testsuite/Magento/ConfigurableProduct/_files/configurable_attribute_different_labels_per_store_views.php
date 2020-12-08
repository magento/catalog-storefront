<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/core_fixturestore.php');

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

$defaultStoreCode = 'default';
$secondStoreCode = 'fixturestore';

$adminStoreViewId = Store::DEFAULT_STORE_ID;
$defaultStoreViewId = $storeManager->getStore($defaultStoreCode)->getId();
$secondStoreViewId = $storeManager->getStore($secondStoreCode)->getId();
/** @var CategorySetup $installer */
$installer = $objectManager->get(CategorySetup::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);

$attribute = $objectManager->get(AttributeFactory::class)->create();
if (!$attribute->loadByCode($entityType, 'first_test_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'frontend_label' => [
                $adminStoreViewId => 'First test attribute',
                $defaultStoreViewId => $defaultStoreCode . ' first test attribute',
                $secondStoreViewId => $secondStoreCode . ' first test attribute'
            ],
            'entity_type_id' => $entityType,
            'frontend_input' => 'select',
            'backend_type' => 'int',
            'is_required' => '0',
            'attribute_code' => 'first_test_attribute',
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_user_defined' => 1,
            'is_unique' => '0',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'option' => [
                'value' => [
                    'option_1' => [
                        $adminStoreViewId => 'First Option 1',
                        $defaultStoreViewId => $defaultStoreCode . ' First Option 1',
                        $secondStoreViewId => $secondStoreCode . ' First Option 1',
                    ],
                    'option_2' => [
                        $adminStoreViewId => 'First Option 2',
                        $defaultStoreViewId => $defaultStoreCode . ' First Option 2',
                        $secondStoreViewId => $secondStoreCode . ' First Option 2',
                    ],
                    'option_3' => [
                        $adminStoreViewId => 'First Option 3',
                        $defaultStoreViewId => $defaultStoreCode . ' First Option 3',
                        $secondStoreViewId => $secondStoreCode . ' First Option 3',
                    ],
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                ],
            ]
        ]
    );

    $attributeRepository->save($attribute);

    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}

$attribute = $objectManager->get(AttributeFactory::class)->create();
if (!$attribute->loadByCode($entityType, 'second_test_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'frontend_label' => [
                $adminStoreViewId => 'Second test attribute',
                $defaultStoreViewId => $defaultStoreCode . ' second test attribute',
                $secondStoreViewId => $secondStoreCode . ' second test attribute'
            ],
            'entity_type_id' => $entityType,
            'frontend_input' => 'select',
            'backend_type' => 'int',
            'is_required' => '0',
            'attribute_code' => 'second_test_attribute',
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_user_defined' => 1,
            'is_unique' => '0',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'option' => [
                'value' => [
                    'second_option_1' => [
                        $adminStoreViewId => 'Second Option 1',
                        $defaultStoreViewId => $defaultStoreCode . ' Second Option 1',
                        $secondStoreViewId => $secondStoreCode . ' Second Option 1'
                    ],
                    'second_option_2' => [
                        $adminStoreViewId => 'Second Option 2',
                        $defaultStoreViewId => $defaultStoreCode . ' Second Option 2',
                        $secondStoreViewId => $secondStoreCode . ' Second Option 2'
                    ]
                ],
                'order' => [
                    'second_option_1' => 1,
                    'second_option_2' => 2
                ],
            ],
        ]
    );

    $attributeRepository->save($attribute);

    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}

$attribute = $objectManager->get(AttributeFactory::class)->create();
if (!$attribute->loadByCode($entityType, 'third_test_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'frontend_label' => [
                $adminStoreViewId => 'Third test attribute',
                $defaultStoreViewId => $defaultStoreCode . ' third test attribute',
                $secondStoreViewId => $secondStoreCode . ' third test attribute'
            ],
            'entity_type_id' => $entityType,
            'frontend_input' => 'select',
            'backend_type' => 'int',
            'is_required' => '0',
            'attribute_code' => 'third_test_attribute',
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_user_defined' => 1,
            'is_unique' => '0',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'option' => [
                'value' => [
                    'third_option_1' => [
                        $adminStoreViewId => 'Third Option 1',
                        $defaultStoreViewId => $defaultStoreCode . ' Third Option 1',
                        $secondStoreViewId => $secondStoreCode . ' Third Option 1'
                    ]
                ],
                'order' => [
                    'third_option_1' => 1
                ],
            ],
        ]
    );

    $attributeRepository->save($attribute);

    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}
