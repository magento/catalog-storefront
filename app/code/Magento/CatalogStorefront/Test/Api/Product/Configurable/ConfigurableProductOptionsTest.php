<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\Downloadable;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\CatalogStorefrontApi\Api\Data\ConfigurableOptionValueArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantsGetRequest;



class ConfigurableProductOptionsTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'configurable';
    const TWO_CHILD_SKU = 'Configurable product';
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'configurable_options' //TODO: confirm this attribute for comparision
    ];

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
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @var ConfigurableOptionValueArrayMapper
     */
    private $arrayMapper;

    /**
     * @var ProductVariantsGetRequest
     */
    private $productVariantInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productVariantInterface = Bootstrap::getObjectManager()->create(ProductVariantsGetRequest::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ConfigurableOptionValueArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Validate configurable product data
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testConfigurableProduct(): void
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $configurableOptions = $product->getExtensionAttributes()->getConfigurableProductOptions()[0];

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $catalogService = $catalogServiceItem->getItems()[0]->getConfigurableOptions();
        $this->assertEquals($configurableOptions->getLabel(), $catalogService->getLabel());
    }

    /**
     * Test product variant
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider variantsProvider
     */
    public function testConfigurableproductVariant(array $variantsProvider)
    {
        $this->markTestSkipped("This test skipped due to: https://github.com/magento/catalog-storefront/issues/304
        and https://github.com/magento/catalog-storefront/issues/27");

        //product variants
        $product = $this->productRepository->get(self::TWO_CHILD_SKU);
        $this->productVariantInterface->setIds([$product->getId()]);
        $this->productVariantInterface->setStoreId(self::STORE_CODE);
        $this->productVariantInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceVariants = $this->catalogService->getProductVariants($this->productVariantInterface);
        self::assertNotEmpty($catalogServiceVariants->getItems());

        //TODO: check why this return empty array
        foreach ($catalogServiceVariants->getItems()[0]->getProduct() as $variant) {
            $price = $variant->getPrice();
            //TODO: Ask for array mapper for variants
        }
    }

    /**
     * Validate configurable product data
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getConfigurableProductOptionProvider
     */
    public function testConfigurableProductOptions(array $expected)
    {
        $product = $this->productRepository->get(self::TWO_CHILD_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getConfigurableOptions() as $productOption) {
            $actual[] = $this->arrayMapper->convertToArray($productOption);
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * //TODO:  ask for data provider values
     * @return array
     */
    public function getConfigurableProductOptionProvider()
    {
        return [
            [
                [
                    [
                        'value_index' => '',
                        'label' => '',
                        'default_label' =>'',
                        'store_label' => '',
                        'use_default_value' => '',
                        'attribute_id' => '',
                        'product_id' => ''
                    ],
                    [
                        'value_index' => '',
                        'label' => '',
                        'default_label' =>'',
                        'store_label' => '',
                        'use_default_value' => '',
                        'attribute_id' => '',
                        'product_id' => ''
                    ],
                ]
            ]
        ];
    }

    public function variantsProvider(): array
    {
        return [
            [
                [
                    [
                        'option_value_id' => '',
                        'regular_price' => 1,
                        'final_price' => '',
                        'scope' => 'default'
                    ],
                    [

                    ]
                ]
            ]
        ];
    }
}
