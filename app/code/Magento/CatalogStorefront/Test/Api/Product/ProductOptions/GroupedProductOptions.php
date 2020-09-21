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
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionMapper;

class GroupedProductOptions extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'grouped';
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
     * @var ProductOptionMapper
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
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductOptionMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Validate grouped product data
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getGroupedProductOptionProvider
     */
    public function testGroupedProductOptions(array $expected)
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
            unset($convertedValues['values'][0]['id']);
            unset($convertedValues['values'][1]['id']);
            $actual[] = $convertedValues;
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for grouped product options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getGroupedProductOptionProvider()
    {
        return [
            [
                [
                    [
                        'id' => 'test_grouped',
                        'label' => 'Grouped Product',
                        'sort_order' => 0,
                        'required' => false,
                        'render_type' => '',
                        'type' => 'super',
                        'values' => [
                            [
                                'label' => 'Simple 11',
                                'sort_order' => '',
                                'default' => false,
                                'image_url' => '',
                                'qty_mutability' => false,
                                'qty' => (float)0,
                                'info_url' => ''
                            ],
                            [
                                'label' => 'Simple 22',
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
            ]
        ];
    }
}
