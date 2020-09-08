<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductShopperInputOptionArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
/**
 * Test class for ProductVariants message bus
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
        'shopper_select_options'
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
     * @var ProductShopperInputOptionArrayMapper
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
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductShopperInputOptionArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Test product shopper input options
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
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

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for shopper input option
     *
     * @return array[][]
     */
    public function shopperInputOptionProvider(): array
    {
        /**
         * Drop-down
        Radio Buttons
        Checkbox
        Multiple Select
         */
        return [
            [
                [
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'Drop down',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'dropdown',

                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi82',
                        'label' => 'Radio buttons',
                        'required' => true,
                        'sort_order' => 0,
                        'render_type' => 'radio',

                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi81',
                        'label' => 'Checkbox',
                        'required' => true,
                        'sort_order' => 2,
                        'render_type' => 'checkbox',
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8xMw==',
                        'label' => 'Multipleselect',
                        'required' => true,
                        'sort_order' => 3,
                        'render_type' => 'multiselect',
                    ]
                ]
            ]
        ];
    }
}
