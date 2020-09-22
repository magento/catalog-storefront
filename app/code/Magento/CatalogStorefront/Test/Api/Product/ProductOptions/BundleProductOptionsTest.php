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
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;

/**
 * Tests configurable product options on the storefront
 */
class BundleProductOptionsTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'bundle-product';
    const STORE_CODE = 'default';

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
     * @magentoDataFixture Magento/CatalogStorefront/_files/bundle_with_multiple_options_and_qty.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getBundleProductOptionProvider
     */
    public function testBundleProductOptions(array $expected)
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getProductOptions() as $productOption) {
            $convertedValues = $this->arrayMapper->convertToArray($productOption);
//            unset($convertedValues['values'][0]['id']);
//            unset($convertedValues['values'][1]['id']);
            $actual[] = $convertedValues;
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
    public function getBundleProductOptionProvider()
    {
        return [
            [
                [
                    [
                        'label' => 'Option 1',
                        'sort_order' => 1,
                        'required' => true,
                        'render_type' => 'select',
                        'type' => 'bundle',
                        'values' => [
                            [
                                'label' => 'Simple Product1',
                                'sort_order' => '0',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => true,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple Product2',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => true,
                                'qty' => (float)2,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'label' => 'Option 2',
                        'sort_order' => 2,
                        'required' => true,
                        'render_type' => 'radio',
                        'type' => 'bundle',
                        'values' => [
                            [
                                'label' => 'Simple Product1',
                                'sort_order' => '0',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => true,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple Product2',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => true,
                                'qty' => (float)2,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'label' => 'Option 3',
                        'sort_order' => 3,
                        'required' => true,
                        'render_type' => 'checkbox',
                        'type' => 'bundle',
                        'values' => [
                            [
                                'label' => 'Simple Product1',
                                'sort_order' => '0',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple Product2',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'label' => 'Option 4',
                        'sort_order' => 4,
                        'required' => true,
                        'render_type' => 'multi',
                        'type' => 'bundle',
                        'values' => [
                            [
                                'label' => 'Simple Product1',
                                'sort_order' => '0',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple Product2',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                        ],
                    ],
                    [
                        'label' => 'Option 5',
                        'sort_order' => 5,
                        'required' => false,
                        'render_type' => 'multi',
                        'type' => 'bundle',
                        'values' => [
                            [
                                'label' => 'Simple Product1',
                                'sort_order' => '0',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple Product2',
                                'sort_order' => '2',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)1,
                                'info_url' => ''
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }
}
