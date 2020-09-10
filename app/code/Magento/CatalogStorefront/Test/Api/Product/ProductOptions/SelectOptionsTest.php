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
class SelectOptionsTest extends StorefrontTestsAbstract
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
        $product = $this->productRepository->get('simple');
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        $index = 0;
        foreach ($catalogServiceItem->getItems()[0]->getOptionsV2() as $productOption) {
            $optionValues = $productOption->getValues();
            foreach ($optionValues as $productOptionValue) {
                $actual[] = $this->arrayMapper->convertToArray($productOptionValue);
                unset($actual[$index]['id']); //id generates randomly, don't need to compare
                $index++;
            }
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for select option values
     *
     * @return array[][]
     */
    public function selectOptionValuesProvider(): array
    {
        return [
            [
                [
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'drop_down option 1',
                        'sort_order' => '1',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'drop_down option 2',
                        'sort_order' => '2',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float) 0,
                        'info_url' => '',
                    ],
                    [
                        //'id' => 'Y3VzdG9tLW9wdGlvbi8x',
                        'label' => 'radio option 1',
                        'sort_order' => '1',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        'label' => 'radio option 2',
                        'sort_order' => '2',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        'label' => 'checkbox option 1',
                        'sort_order' => '1',
                        'default' => false,
                        'image_url' => "",
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        'label' => 'checkbox option 2',
                        'sort_order' => '2',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        'label' => 'multiple option 1',
                        'sort_order' => '1',
                        'default' => false,
                        'image_url' => '',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                    [
                        'label' => 'multiple option 2',
                        'sort_order' => '2',
                        'default' => false,
                        'image_url' => '2',
                        'qty_mutability' => false,
                        'qty' => (float)0,
                        'info_url' => '',
                    ],
                ]
            ]
        ];
    }
}
