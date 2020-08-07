<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Tests configurable product export
 * @magentoAppIsolation enabled
 */
class ConfigurableProductExportTest extends AbstractProductExportTestHelper
{
    /**
     * Attributes to compare for configurable product
     *
     * @var string[]
     */
    protected $attributesToCompare = [
        'sku',
        'name',
        'type',
        'status',
        'tax_class_id',
        'created_at',
        'updated_at',
        'url_key',
        'visibility',
        'currency',
        'displayable',
        'buyable',
        'categories',
        'in_stock',
        'low_stock',
        'url',
    ];
    
    /**
     * Option values to compare for configurable products
     *
     * @var string[]
     */
    protected $optionsToCompare = [
        'id',
        'type',
        'title',
        'sort_order',
        'attribute_id',
        'attribute_code',
        'use_default'
    ];

    /**
     * Option 'value' values to compare for configurable products
     *
     * @var string[]
     */
    protected $optionValuesToCompare = [
        'id',
        'label',
        'default_label',
        'store_label',
        'use_default_value'
    ];

    /**
     * Test product export REST API
     *
     * @magentoApiDataFixture Magento/CatalogRule/_files/configurable_product.php
     *
     * @return void
     */
    public function testExport(): void
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');
        $this->runIndexer();

        try {
            $product = $this->productRepository->get('configurable');
        } catch (NoSuchEntityException $e) {
            $this->fail("Couldn`t find product with sku 'simple' " . $e->getMessage());
        }

        if (isset($product)) {
            $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $product->getId();
            $result = $this->_webApiCall($this->createServiceInfo, []);
            $this->assertProductsEquals($this->productsFeed->getFeedByIds([$product->getId()])['feed'], $result);
        }
    }
}
