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
    protected $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento_CatalogStorefront::Test/Api/Product/Downloadable/_files/product_downloadable_with_link_url_and_sample_url_override.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithUrls(): void
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $productSampleData = $product->getExtensionAttributes()->getDownloadableProductSamples()[0];

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['samples']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $catalogServiceSamples = $catalogServiceItem->getItems()[0]->getSamples()[0];
        $this->assertEquals($productSampleData->getTitle(), $catalogServiceSamples->getLabel());
        $this->assertEquals($productSampleData->getSampleUrl(), $catalogServiceSamples->getUrl());
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithFiles(): void
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $productSampleData = $product->getExtensionAttributes()->getDownloadableProductSamples()[0];

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['samples']);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        $catalogServiceSamples = $catalogServiceItem->getItems()[0]->getSamples()[0];
        $this->assertEquals($productSampleData->getTitle(), $catalogServiceSamples->getLabel());
        $this->assertEquals($productSampleData->getSampleFile(), $catalogServiceSamples->getUrl());
    }
}
