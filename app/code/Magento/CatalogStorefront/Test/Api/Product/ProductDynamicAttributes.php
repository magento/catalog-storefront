<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for downloadable product exporter
 *
 */
class ProductDynamicAttributes extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const STORE_CODE = 'default';

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var ProductsGetRequestInterface
     */
    private $productsGetRequestInterface;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductArrayMapper
     */
    private $arrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductArrayMapper::class);
    }

    /**
     * Validate product with boolean attribute
     *
     * @magentoDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_boolean_attribute.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     */
    public function testProductBooleanAttribute(): void
    {
        $expected = [
            [
                'code' => 'boolean_attribute',
                'type' => 'boolean',
                'values' => ['yes']
            ]
        ];
        $actual = $this->getApiResult('simple_with_boolean');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    /**
     * Validate product with multiselect attribute
     *
     * @magentoApiDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_multiselect_attribute.php
     * @magentoDbIsolation disabled
     */
    public function testProductMultiselectAttribute(): void
    {
        $expected = [
            [
                'code' => 'multiselect_attribute',
                'type' => 'multiselect',
                'values' => ['Option 1']
            ]
        ];
        $actual = $this->getApiResult('simple_with_multiselect');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    /**
     * Validate product with image attribute
     *
     * @magentoApiDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_image_attribute.php
     * @magentoDbIsolation disabled
     */
    public function testProductImageAttribute(): void
    {
        $expected = [
            [
                'code' => 'image_attribute',
                'type' => 'media_image',
                'values' => ['imagepath']
            ]
        ];
        $actual = $this->getApiResult('simple_with_image');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    /**
     * Validate product with decimal attribute
     *
     * @magentoApiDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_decimal_attribute.php
     * @magentoDbIsolation disabled
     */
    public function testProductDecimalAttribute(): void
    {
        $expected = [
            [
                'code' => 'decimal_attribute',
                'type' => 'price',
                'values' => ['100.000000']
            ]
        ];
        $actual = $this->getApiResult('simple_with_decimal');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    /**
     * Validate product with text editor attribute
     *
     * @magentoApiDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_text_editor_attribute.php
     * @magentoDbIsolation disabled
     */
    public function testProductTextEditorAttribute(): void
    {
        $expected = [
            [
                'code' => 'text_editor_attribute',
                'type' => 'textarea',
                'values' => ['text Editor Attribute test']
            ]
        ];
        $actual = $this->getApiResult('simple_with_text_editor');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    /**
     * Validate product with date attribute
     *
     * @magentoApiDataFixture Magento_CatalogExport::Test/Api/_files/one_product_simple_with_date_attribute.php
     * @magentoDbIsolation disabled
     */
    public function testProductDateAttribute(): void
    {
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple_with_date');

        $expected = [
            [
                'code' => 'date_attribute',
                'type' => 'date',
                'values' => [$product->getData('date_attribute')]
            ]
        ];
        $actual = $this->getApiResult('simple_with_date');
        self::assertArrayHasKey('attributes', $actual);
        self::assertEquals($expected, $actual['attributes']);
    }

    public function getApiResult($sku) : array
    {
        $product = $this->productRepository->get($sku);
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['attributes']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        return $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]);
    }
}
