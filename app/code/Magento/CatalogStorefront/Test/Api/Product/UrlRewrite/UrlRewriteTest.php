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
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for product url rewrite data exporter
 */
class UrlRewriteTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    private const TEST_SKU = 'simple333';
    private const STORE_CODE = 'default';
    private const KEY_URL = 'url';
    private const KEY_PARAMETERS = 'parameters';

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
     * @var UrlRewriteArrayMapper
     */
    private $urlRewriteArrayMapper;

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
        $this->urlRewriteArrayMapper = Bootstrap::getObjectManager()->create(UrlRewriteArrayMapper::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
    }

    /**
     * Validate url rewrite for product
     *
     * @param array $urlRewriteProvider
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @dataProvider getProductUrlRewriteProvider
     *
     * @magentoDbIsolation disabled
     *
     */
    public function testProductUrlRewriteData(array $urlRewriteProvider): void
    {
        $baseUrl = $this->storeManager->getStore(self::STORE_CODE)->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $product = $this->productRepository->get(self::TEST_SKU);

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

        // append base store url to expected urls
        foreach ($urlRewriteProvider as $key => $item) {
            if (isset($item[self::KEY_URL])) {
                $urlRewriteProvider[$key][self::KEY_URL] = $baseUrl . $item[self::KEY_URL];
            }
        }

        $this->compare($urlRewriteProvider, $actualData);
    }

    /**
     * Get url rewrite provider for product
     *
     * @return array
     */
    public function getProductUrlRewriteProvider(): array
    {
        return [
            [
                [
                    [
                        self::KEY_URL => 'simple-product-three.html',
                        self::KEY_PARAMETERS => [
                            [
                                'name' => 'id',
                                'value' => '333'
                            ]
                        ]
                    ],
                    [
                        self::KEY_URL => 'category-1/simple-product-three.html',
                        self::KEY_PARAMETERS => [
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
}
