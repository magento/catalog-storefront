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
use Magento\CatalogStorefrontApi\Api\Data\ProductShopperInputOptionArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;

/**
 * Tests shopper input options on the store front
 */
class ShopperInputOptionsTest extends StorefrontTestsAbstract
{
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'shopper_input_options'
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
     * @var ProductShopperInputOptionArrayMapper
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
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductShopperInputOptionArrayMapper::class);
    }

    /**
     * Test product shopper input options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @dataProvider shopperInputOptionProvider
     */
    public function testShopperInputOptionData(array $expected): void
    {
        $product = $this->productRepository->get('simple');

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getShopperInputOptions() as $item) {
            $actual[] = $this->arrayMapper->convertToArray($item);
        }

        $this->compare($expected, $actual);
    }

    /**
     * Data provider for shopper input option
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function shopperInputOptionProvider(): array
    {
        return [
            [
                [
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'Test Field',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'field',
                        'price' => [
                            [
                                'scope' => 'NOT LOGGED IN',
                                'regular_price' => 1.0,
                                'final_price' => 1.0
                            ],
                            [
                                'scope' => 'General',
                                'regular_price' => 1.0,
                                'final_price' => 1.0
                            ],
                            [
                                'scope' => 'Wholesale',
                                'regular_price' => 1.0,
                                'final_price' => 1.0
                            ],
                            [
                                'scope' => 'Retailer',
                                'regular_price' => 1.0,
                                'final_price' => 1.0
                            ]
                        ],
                        'range' => [
                            'to' => 100.0,
                        ],
                        'file_extension' => [],
                        'image_size_x' => 0,
                        'image_size_y' => 0
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi82',
                        'label' => 'Test Date and Time',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'date_time',
                        'price' => [
                            [
                                'scope' => 'NOT LOGGED IN',
                                'regular_price' => 2.0,
                                'final_price' => 2.0
                            ],
                            [
                                'scope' => 'General',
                                'regular_price' => 2.0,
                                'final_price' => 2.0
                            ],
                            [
                                'scope' => 'Wholesale',
                                'regular_price' => 2.0,
                                'final_price' => 2.0
                            ],
                            [
                                'scope' => 'Retailer',
                                'regular_price' => 2.0,
                                'final_price' => 2.0
                            ]
                        ],
                        'range' => [
                            'to' => 0.0,
                        ],
                        'file_extension' => [],
                        'image_size_x' => 0,
                        'image_size_y' => 0
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi81',
                        'label' => 'area option',
                        'required' => true,
                        'sort_order' => 2,
                        'render_type' => 'area',
                        'price' => [
                            [
                                'scope' => 'NOT LOGGED IN',
                                'regular_price' => 0.395,
                                'final_price' => 0.395
                            ],
                            [
                                'scope' => 'General',
                                'regular_price' => 0.395,
                                'final_price' => 0.395
                            ],
                            [
                                'scope' => 'Wholesale',
                                'regular_price' => 0.395,
                                'final_price' => 0.395
                            ],
                            [
                                'scope' => 'Retailer',
                                'regular_price' => 0.395,
                                'final_price' => 0.395
                            ]
                        ],
                        'range' => [
                            'to' => 20.0,
                        ],
                        'file_extension' => [],
                        'image_size_x' => 0,
                        'image_size_y' => 0
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8xMw==',
                        'label' => 'File option',
                        'required' => true,
                        'sort_order' => 3,
                        'render_type' => 'file',
                        'price' => [
                            [
                                'scope' => 'NOT LOGGED IN',
                                'regular_price' => 3.0,
                                'final_price' => 3.0
                            ],
                            [
                                'scope' => 'General',
                                'regular_price' => 3.0,
                                'final_price' => 3.0
                            ],
                            [
                                'scope' => 'Wholesale',
                                'regular_price' => 3.0,
                                'final_price' => 3.0
                            ],
                            [
                                'scope' => 'Retailer',
                                'regular_price' => 3.0,
                                'final_price' => 3.0
                            ]
                        ],
                        'range' => [
                            'to' => 0.0,
                        ],
                        'file_extension' => ['jpg, png, gif'],
                        'image_size_x' => 10,
                        'image_size_y' => 20
                    ]
                ]
            ]
        ];
    }
}
