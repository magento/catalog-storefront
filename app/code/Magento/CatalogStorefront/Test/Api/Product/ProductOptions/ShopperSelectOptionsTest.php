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
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionValueArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;

/**
 * Test class for Select custom options
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShopperSelectOptionsTest extends StorefrontTestsAbstract
{
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'options_v2'
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
    protected $productRepository;

    /**
     * @var ProductOptionValueArrayMapper
     */
    protected $arrayMapper;

    /**
     * @var CompareArraysRecursively
     */
    protected $compareArraysRecursively;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductOptionValueArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Test product shopper select options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider shopperSelectOptionProvider
     */
    public function testShopperSelectOptionData(array $expected): void
    {
        //TODO: Waiting for Ruslan Kostiv to finalize this testing, as I need to revise this based on his changes
        $product = $this->productRepository->get('simple_with_custom_options');

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getOptionsV2() as $productOption) {
            $optionValues = $productOption->getValues();
            foreach ($optionValues as $productOptionValue) {
                $actual[] = $this->arrayMapper->convertToArray($productOptionValue);
            }
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for shopper select option
     *
     * @return array[][]
     */
    public function shopperSelectOptionProvider(): array
    {
        return [
            [
                [
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'Test Select',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'drop_down',
                        'value' => [
                            'option_type_id' => null,
                            'title'         => 'Option 1',
                            'price'         => 3,
                            'price_type'    => 'fixed',
                            'sku'           => '3-1-select',
                        ],
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi82',
                        'label' => 'Test Radio',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'radio',
                        'value' =>  [
                            'option_type_id' => null,
                            'title'         => 'Option 1',
                            'price'         => 3,
                            'price_type'    => 'fixed',
                            'sku'           => '4-1-radio',
                        ],
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi81',
                        'label' => 'checkbox option',
                        'required' => true,
                        'sort_order' => 6,
                        'render_type' => 'checkbox',
                        'value' =>     [
                            'title' => 'checkbox option 1',
                            'price' => 10,
                            'price_type' => 'fixed',
                            'sku' => 'checkbox option 1 sku',
                            'sort_order' => 1,
                        ],
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8xMw==',
                        'label' => 'multiple option',
                        'required' => true,
                        'sort_order' => 7,
                        'render_type' => 'multiple',
                        'value' => [
                            'title' => 'multiple option 1',
                            'price' => 10,
                            'price_type' => 'fixed',
                            'sku' => 'multiple option 1 sku',
                            'sort_order' => 1,
                        ],
                    ]
                ]
            ]
        ];
    }
}
