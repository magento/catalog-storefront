<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Tests simple product export
 * @magentoAppIsolation enabled
 */
class SimpleProductExportTest extends AbstractProductExportTestHelper
{
    /**
     * @var string[]
     */
    protected $attributesToCompare = [
        'sku',
        'name',
        'type',
        'status',
        'taxClassId',
        'createdAt',
        'updatedAt',
        'urlKey',
        'visibility',
        'currency',
        'displayable',
        'buyable',
        'attributes',
        'categories',
        'inStock',
        'lowStock',
        'url',
        'image',
        'smallImage',
        'entered_options'
    ];

    /**
     * Test product export REST API
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     *
     * @return void
     */
    public function testExport(): void
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');
        $this->runIndexer();

        try {
            $product = $this->productRepository->get('simple');
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
