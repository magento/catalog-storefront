<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\UrlRewriteArrayMapper;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for product url rewrite data exporter
 */
class UrlRewriteTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    private const TEST_SKU = 'simple';
    private const TEST_WITH_CATEGORY_SKU = 'simple333';
    private const STORE_CODE = 'default';

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
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var UrlRewriteArrayMapper
     */
    private $urlRewriteArrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayUtils = Bootstrap::getObjectManager()->create(ArrayUtils::class);
        $this->urlRewriteArrayMapper = Bootstrap::getObjectManager()->create(UrlRewriteArrayMapper::class);
    }

    /**
     * Validate url rewrite for product not assigned to any category
     *
     * @param array $urlRewriteProvider
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getUrlRewriteProvider
     *
     * @magentoDbIsolation disabled
     *
     */
    public function testUrlRewriteData(array $urlRewriteProvider): void
    {
        $this->processTestUrlRewrites(self::TEST_SKU, $urlRewriteProvider);
    }

    /**
     * Get url rewrite provider
     *
     * @return array
     */
    public function getUrlRewriteProvider(): array
    {
        return [
            [
                [
                    [
                        'url' => 'simple-product.html',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Validate url rewrite for product not assigned to any category
     *
     * @param array $urlRewriteProvider
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @dataProvider getProductWithCategoryUrlRewriteProvider
     *
     * @magentoDbIsolation disabled
     *
     */
    public function testProductWithCategoryUrlRewriteData(array $urlRewriteProvider): void
    {
        $this->processTestUrlRewrites(self::TEST_WITH_CATEGORY_SKU, $urlRewriteProvider);
    }


    /**
     * Get url rewrite provider for product assigned to category
     *
     * @return array
     */
    public function getProductWithCategoryUrlRewriteProvider(): array
    {
        return [
            [
                [
                    [
                        'url' => 'simple-product-three.html',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'value' => '333'
                            ]
                        ]
                    ],
                    [
                        'url' => 'category-1/simple-product-three.html',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'value' => '333'
                            ],
                            [
                                'name' => 'category',
                                'value' => '333'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Process testing of product url rewirtes
     *
     * @param string $sku
     * @param array $urlRewriteProvider
     * @return \Magento\CatalogStorefrontApi\Api\Data\ProductInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     */
    private function processTestUrlRewrites(string $sku, array $urlRewriteProvider) : void
    {
        $product = $this->productRepository->get($sku);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['url_rewrites']);

        $catalogServiceItems = $this->catalogService->getProducts($this->productsGetRequestInterface)->getItems();

        $this->assertNotEmpty($catalogServiceItems);
        $item = \array_shift($catalogServiceItems);
        $actualData = [];

        foreach ($item->getUrlRewrites() as $urlRewrite) {
            $actualData[] = $this->urlRewriteArrayMapper->convertToArray($urlRewrite);
        }

        $this->assertNotEmpty($actualData);

        $diff = $this->arrayUtils->recursiveDiff($urlRewriteProvider, $actualData);
        self::assertEquals([], $diff);
    }
}
