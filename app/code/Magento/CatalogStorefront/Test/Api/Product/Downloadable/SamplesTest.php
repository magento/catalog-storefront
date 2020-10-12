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
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

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
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento_CatalogStorefront::Test/Api/Product/Downloadable/_files/sf_product_downloadable_with_urls.php
     * @dataProvider downloadableUrlsProvider
     *
     * @magentoDbIsolation disabled
     *
     * @param array $expected
     *
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithUrls(array $expected): void
    {
        $this->validateSampleData($expected);
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @dataProvider downloadableFilesProvider
     *
     * @magentoDbIsolation disabled
     *
     * @param array $expected
     *
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithFiles(array $expected): void
    {
        $this->validateSampleData($expected);
    }

    /**
     * Validate sample data
     *
     * @param array $dataProvider
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    private function validateSampleData(array $dataProvider) : void
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['samples']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actual = $this->arrayMapper->convertToArray($catalogServiceItem->getItems()[0]->getSamples()[0]);

        $this->compare($dataProvider, $actual);

        $baseUrl = $this->storeManager->getStore(self::STORE_CODE)->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $sample = $product->getExtensionAttributes()->getDownloadableProductSamples()[0];

        self::assertEquals(
            \sprintf('%sdownloadable/download/sample/sample_id/%s', $baseUrl, $sample->getId()),
            $actual['resource']['url']
        );
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
                        'label' => 'Downloadable Product Sample Title',
                        'roles' => [],
                    ],
                    'sort_order' => '0',
                ],
            ],
        ];
    }
}
