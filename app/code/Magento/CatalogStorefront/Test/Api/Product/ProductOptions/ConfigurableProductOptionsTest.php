<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\ProductOptions;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionArrayMapper;

/**
 * Tests configurable product options on the storefront
 */
class ConfigurableProductOptionsTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'configurable';
    const STORE_CODE = 'default';
    const FIXTURE_STORE = 'fixturestore';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'product_options'
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
     * @var ProductOptionArrayMapper
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
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductOptionArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Validate configurable product data
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_different_option_labels_per_store_views.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @param string $storeCode
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getConfigurableProductOptionsInTwoStores
     */
    public function testConfigurableProductOptionsTwoStores(string $storeCode, array $expected)
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore($storeCode);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItemDefaultStore = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItemDefaultStore->getItems());

        $actual = [];
        foreach ($catalogServiceItemDefaultStore->getItems()[0]->getProductOptions() as $productOption) {
            $actual[] = $this->arrayMapper->convertToArray($productOption);
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );

        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for configurable product options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getConfigurableProductOptionsInTwoStores()
    {
        $storeCodes = [self::STORE_CODE, self::FIXTURE_STORE];
        $expectedData = [];
        foreach ($storeCodes as $storeCode) {
            $expectedData[$storeCode] = [
                $storeCode,
                [
                    [
                        'id' => 'first_test_attribute',
                        'label' => $storeCode . ' first test attribute',
                        'sort_order' => 0,
                        'required' => false,
                        'render_type' => '',
                        'type' => 'configurable',
                        'values' => [
                            [
                                'label' => $storeCode . ' First Option 1',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                            [
                                'label' => $storeCode . ' First Option 2',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                            [
                                'label' => $storeCode . ' First Option 3',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'id' => 'second_test_attribute',
                        'label' => $storeCode . ' second test attribute',
                        'sort_order' => 1,
                        'required' => false,
                        'render_type' => '',
                        'type' => 'configurable',
                        'values' => [
                            [
                                'label' => $storeCode . ' Second Option 1',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                            [
                                'label' => $storeCode . ' Second Option 2',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'id' => 'third_test_attribute',
                        'label' => $storeCode . ' third test attribute',
                        'sort_order' => 2,
                        'required' => false,
                        'render_type' => '',
                        'type' => 'configurable',
                        'values' => [
                            [
                                'label' => $storeCode . ' Third Option 1',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                        ],
                    ],
                ]
            ];
        }

        return $expectedData;
    }
}
