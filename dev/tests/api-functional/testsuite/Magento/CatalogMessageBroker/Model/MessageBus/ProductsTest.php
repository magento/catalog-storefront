<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\Product\ProductsConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Products message bus
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsTest extends StorefrontTestsAbstract
{
    const TEST_SKU = 'in-stock-product';
    const STORE_CODE = 'default';
    const ERROR_MESSAGE = 'Products with the following ids are not found in catalog: %s';

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Validate deleted products are removed from StoreFront
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Throwable
     */
    public function testSaveAndDeleteProduct() : void
    {
        $product = $this->getProduct(self::TEST_SKU);
        $this->assertEquals(self::TEST_SKU, $product->getSku());
        $entitiesData = [
            [
                'entity_id' => (int) $product->getId(),
            ]
        ];
        $productFeed = $this->productFeed->getFeedByIds([(int)$product->getId()], [self::STORE_CODE]);
        $this->assertNotEmpty($productFeed);

        $updateMessage = $this->messageBuilder->build(
            ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
            $entitiesData,
            self::STORE_CODE
        );
        $this->productsConsumer->processMessage($updateMessage);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());
        $item = $catalogServiceItem->getItems()[0];
        $this->assertEquals($item->getSku(), $product->getSku());

        $this->deleteProduct($product->getSku());
        $deletedFeed = $this->productFeed->getDeletedByIds([(int)$product->getId()], [self::STORE_CODE]);
        $this->assertNotEmpty($deletedFeed);

        $deleteMessage = $this->messageBuilder->build(
            ProductsConsumer::PRODUCTS_DELETED_EVENT_TYPE,
            $entitiesData,
            self::STORE_CODE
        );

        $this->productsConsumer->processMessage($deleteMessage);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(self::ERROR_MESSAGE, $product->getId()));
        $this->catalogService->getProducts($this->productsGetRequestInterface);
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProduct(string $sku) : ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }

    /**
     * @param string $sku
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function deleteProduct(string $sku) : void
    {
        try {
            $registry = Bootstrap::getObjectManager()->get(Registry::class);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', true);
            $this->productRepository->deleteById($sku);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', false);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }
}
