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
use Magento\CatalogStorefrontApi\Api\Data\SampleArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;

/**
 * Test for downloadable product exporter
 *
 */
class SamplesTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'downloadable-product';
    const STORE_CODE = 'default';

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
     * @var SampleArrayMapper
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
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(SampleArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_link_url_and_sample_url.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @dataProvider downloadableUrlsProvider
     */
    public function testDownloadableProductsWithUrls(array $expected): void
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['samples']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $actual = $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]->getSamples()[0]);

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @dataProvider downloadableFilesProvider
     */
    public function testDownloadableProductsWithFiles(array $expected): void
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['samples']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $actual = $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]->getSamples()[0]);

        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        self::assertEquals([], $diff, "Actual response doesn't equal expected data");
    }

    /**
     * Data provider for downloadable URLs
     *
     * @return array
     */
    public function downloadableUrlsProvider(): array
    {
        return [
            [
                [
                    'url' => 'http://example.com/downloadable.txt',
                    'label' => 'Downloadable Product Sample Title',
                    'sort_order' => 0
                ]
            ]
        ];
    }

    /**
     * Data provider for downloadable files
     *
     * @return array
     */
    public function downloadableFilesProvider(): array
    {
        return [
            [
                [
                    'url' => '/j/e/jellyfish_1_4.jpg',
                    'label' => 'Downloadable Product Sample Title',
                    'sort_order' => 0
                ]
            ]
        ];
    }
}
