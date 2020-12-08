<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Product storefront service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsTest extends StorefrontTestsAbstract
{
    const TEST_SKU = 'in-stock-product';
    const STORE_CODE = 'default';
    const ERROR_MESSAGE = 'Products with the following ids are not found in catalog: %s';

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
     * Validate deleted products are removed from StoreFront
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Throwable
     */
    public function testSaveAndDeleteProduct(): void
    {
        $product = $this->getProduct(self::TEST_SKU);
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());
        $item = $catalogServiceItem->getItems()[0];
        $this->assertEquals($item->getSku(), $product->getSku());

        $this->deleteProduct($product->getSku());
        $this->runConsumers(['catalog.product.export.consumer']);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(self::ERROR_MESSAGE, $product->getId()));
        $this->catalogService->getProducts($this->productsGetRequestInterface);
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProduct(string $sku): ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }

    /**
     * Delete product from database
     *
     * @param string $sku
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function deleteProduct(string $sku): void
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
