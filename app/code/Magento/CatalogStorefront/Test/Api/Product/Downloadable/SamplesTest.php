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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(SampleArrayMapper::class);
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento_CatalogStorefront::Test/Api/Product/Downloadable/_files/sf_product_downloadable_with_urls.php
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
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]->getSamples()[0]);

        $this->compare($expected, $actual);
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
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]->getSamples()[0]);

        $this->compare($expected, $actual);
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
                    'resource' => [
                        'url' => 'http://example.com/downloadable.txt',
                        'label' => 'Downloadable Product Sample',
                        'roles' => [],
                    ],
                    'sort_order' => '10',
                ],
            ],
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
                    'resource' => [
                        'url' => '/j/e/jellyfish_1_4.jpg',
                        'label' => 'Downloadable Product Sample Title',
                        'roles' => [],
                    ],
                    'sort_order' => '0',
                ],
            ],
        ];
    }
}
