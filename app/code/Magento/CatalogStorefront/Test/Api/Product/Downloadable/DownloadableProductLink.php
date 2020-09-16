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
use Magento\CatalogStorefrontApi\Api\Data\DownloadableLinkArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantsGetRequest;

/**
 * Test Class for Downloadable Product Link
 */
class DownloadableProductLink extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'downloadable-product';
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'downloadable_options' //TODO: confirm this attribute for comparision
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
     * @var DownloadableLinkArrayMapper
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
        $this->arrayMapper = Bootstrap::getObjectManager()->create(DownloadableLinkArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
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
    public function testDownloadableProductLink(array $expected)
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $actual = [];
        foreach ($catalogServiceItem->getItems()[0]->getDownloadableProductLinks() as $productOptionLink) {
            $actual[] = $this->arrayMapper->convertToArray($productOptionLink);
        }

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * //TODO: ask for data provider values
     * @return array
     */
    public function getConfigurableProductOptionProvider()
    {
        return [
            [
                [
                    [
                        'sample_url' => '',
                        'title' => '',
                        'sort_order' => '',
                        'sample_type' => '',
                        'sample_file' => '',
                        'link_id' => '',
                        'price' => '',
                        'link_type' => '',
                        'is_shareable' => '',
                        'number_of_downloads' => '',
                        'entity_id' => ''
                    ],
                ]
            ]
        ];
    }
}
