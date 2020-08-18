<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Test\Integration\CatalogService;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Throwable;
use Zend_Db_Statement_Exception;

class ProductsTest extends AbstractProductTestHelper
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productsConsumer = Bootstrap::getObjectManager()->create(ProductsConsumer::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
    }

    /**
     * Validate deleted products are removed from StoreFront
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @throws StateException
     * @throws Throwable
     * @throws Zend_Db_Statement_Exception
     */
    public function testDeleteProduct()
    {
        $product = $this->getProduct(self::TEST_SKU);
        $this->assertEquals(self::TEST_SKU, $product->getSku());
        $this->productsConsumer->processMessage("[\"" . $product->getId() . "\"]");
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore("1");
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $item = $catalogServiceItem->getItems()[0];
        $this->assertEquals($item->getSku(), $product->getSku());
        $this->deleteProduct($product->getSku());
        $extractedProduct = $this->getExtractedProduct(self::TEST_SKU, self::STORE_CODE);
        $this->assertEquals(1, (int)$extractedProduct['is_deleted']);
        $this->productsConsumer->processMessage("[\"" . $product->getId() . "\"]");
        try {
            $this->catalogService->getProducts($this->productsGetRequestInterface);
            $this->fail('Item has not been deleted');
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals(sprintf(self::ERROR_MESSAGE, $product->getId()), $exception->getMessage());
        }
    }

    /**
     * @param $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct($sku)
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }

    /**
     * @param $sku
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteProduct($sku)
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
