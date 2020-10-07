<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefront\Test\Api\Product\ProductOptions;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Select custom options
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectedOptionsTest extends StorefrontTestsAbstract
{
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'product_options',
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
     * @var ProductOptionArrayMapper
     */
    private $productOptionArrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->productOptionArrayMapper = Bootstrap::getObjectManager()->create(ProductOptionArrayMapper::class);
    }

    /**
     * Test product select options
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider selectOptionValuesProvider
     */
    public function testSelectOptionData(array $expected): void
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->productRepository->get('simple');
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actualOptions = [];
        foreach ($catalogServiceItem->getItems()[0]->getProductOptions() as $productOption) {
            /** @var \Magento\CatalogStorefrontApi\Api\Data\ProductOption $convertedOptions */
            $convertedOptions = $this->productOptionArrayMapper->convertToArray($productOption);
            $actualOptions[] = $convertedOptions;
        }

        $this->compare($expected, $actualOptions);
    }

    /**
     * Data provider for select option values
     *
     * @return array[][]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function selectOptionValuesProvider(): array
    {
        return [
            [
                [
                    [
                        'label' => 'drop_down option',
                        'sort_order' => 4,
                        'required' => true,
                        'render_type' => 'drop_down',
                        'type' => 'custom_option',
                        'values' => [
                            [
                                'label' => 'drop_down option 1',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],

                            [
                                'label' => 'drop_down option 2',
                                'sort_order' => '2',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                        ],
                    ],
                    [
                        'label' => 'radio option',
                        'sort_order' => 5,
                        'required' => true,
                        'render_type' => 'radio',
                        'type' => 'custom_option',
                        'values' => [
                            [
                                'label' => 'radio option 1',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                            [
                                'label' => 'radio option 2',
                                'sort_order' => '2',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                        ],
                    ],
                    [
                        'label' => 'checkbox option',
                        'sort_order' => 6,
                        'required' => true,
                        'render_type' => 'checkbox',
                        'type' => 'custom_option',
                        'values' => [
                            [
                                'label' => 'checkbox option 1',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                            [
                                'label' => 'checkbox option 2',
                                'sort_order' => '2',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                        ],
                    ],
                    [
                        'label' => 'multiple option',
                        'sort_order' => 7,
                        'required' => true,
                        'render_type' => 'multiple',
                        'type' => 'custom_option',
                        'values' => [
                            [
                                'label' => 'multiple option 1',
                                'sort_order' => '1',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                            [
                                'label' => 'multiple option 2',
                                'sort_order' => '2',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => 0.0,
                                'info_url' => '',
                                'price' => 0.0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
