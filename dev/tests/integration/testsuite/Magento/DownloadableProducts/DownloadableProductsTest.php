<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableProductDataExporter\Test\Integration;

use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\Product\ProductsConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for downloadable product exporter
 *
 */
class DownloadableProductsTest extends AbstractProductTestHelper
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'downloadable-product';
    const STORE_CODE = 'default';

    /**
     * @var ProductsConsumer
     */
    private $productsConsumer;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var ProductsGetRequestInterface
     */
    private $productsGetRequestInterface;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var FeedInterface
     */
    private $productFeed;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productsConsumer = Bootstrap::getObjectManager()->create(ProductsConsumer::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->messageBuilder = Bootstrap::getObjectManager()->create(ChangedEntitiesMessageBuilder::class);
        $this->productFeed = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed('products');
    }

    /**
     * Validate downloadable product data
     *
     * @magentoDataFixture Magento/DownloadableProducts/_files/product_downloadable_with_link_url_and_sample_url_override.php
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithUrls() : void
    {
        $this->runIndexer();

        $product = $this->productRepository->get(self::TEST_SKU);
        $this->assertEquals(self::TEST_SKU, $product->getSku());

        $productFeed = $this->productFeed->getFeedByIds([(int)$product->getId()], [self::STORE_CODE]);
        $this->assertNotEmpty($productFeed['feed']);

        $productSampleData = $product->getExtensionAttributes()->getDownloadableProductSamples()[0];
        $extractedSampleData = $productFeed['feed'][0]['samples'][0];
        $this->assertEquals($productSampleData->getTitle(), $extractedSampleData['label']);
        $this->assertEquals($productSampleData->getSampleUrl(), $extractedSampleData['url']);

        $updateMessage = $this->messageBuilder->build(
            [(int)$product->getId()],
            ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
            self::STORE_CODE
        );
        $this->productsConsumer->processMessage($updateMessage);

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
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testDownloadableProductsWithFiles() : void
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $this->assertEquals(self::TEST_SKU, $product->getSku());

        $productFeed = $this->productFeed->getFeedByIds([(int)$product->getId()], [self::STORE_CODE]);
        $this->assertNotEmpty($productFeed['feed']);

        $productSampleData = $product->getExtensionAttributes()->getDownloadableProductSamples()[0];
        $extractedSampleData = $productFeed['feed'][0]['samples'][0];
        $this->assertEquals($productSampleData->getTitle(), $extractedSampleData['label']);
        $this->assertEquals($productSampleData->getSampleFile(), $extractedSampleData['url']);

        $updateMessage = $this->messageBuilder->build(
            [(int)$product->getId()],
            ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
            self::STORE_CODE
        );
        $this->productsConsumer->processMessage($updateMessage);

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
