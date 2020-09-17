<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\ProductVariants;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;

/**
 * Tests configurable product variants on the storefront
 */
class ConfigurableProductVariantsTest extends StorefrontTestsAbstract
{
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'variants'
    ];

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var ProductVariantsGetRequestInterface
     */
    private $productVariantsGetRequestInterface;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductVariantArrayMapper
     */
    private $arrayMapper;

    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productVariantsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductVariantsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductVariantArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Test configurable product variants
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @dataProvider configurableProductVariantsProvider
     */
    public function testConfigurableProductVariantsData(array $expected): void
    {
        $configurableProduct = $this->productRepository->get('Configurable product');

        $this->productVariantsGetRequestInterface->setIds([$configurableProduct->getId()]);
        $this->productVariantsGetRequestInterface->setStoreId(self::STORE_CODE);
        $this->productVariantsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProductVariants($this->productVariantsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getAttributes() as $item) {
            $actual[] = $this->arrayMapper->convertToArray($item);
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for configurable product variants
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function configurableProductVariantsProvider(): array
    {
        return [
            [
                [
                    [
                        'label' => 'Option 1',
                        'code' => 'test_configurable',
                        'value_index' => '13',
                        'attribute_id' => '179'
                    ],
                    [
                        'label' => 'Option 2',
                        'code' => 'test_configurable',
                        'value_index' => '14',
                        'attribute_id' => '179'
                    ]
                ]
            ]
        ];
    }
}