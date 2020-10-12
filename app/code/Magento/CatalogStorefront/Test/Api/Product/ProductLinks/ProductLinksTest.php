<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\ProductLinks;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\LinkArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for product links (related, crosssell, upsell, associated)
 */
class ProductLinksTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
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
     * @var LinkArrayMapper
     */
    private $linkArrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->linkArrayMapper = Bootstrap::getObjectManager()->create(LinkArrayMapper::class);
    }

    /**
     * Validate crosssell link data
     *
     * @param array $dataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     * @dataProvider getCrosssellLinkProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     */
    public function testCrosssellData(array $dataProvider) : void
    {
        $product = $this->productRepository->get('simple_with_cross');
        $this->validateLinkData($product, $dataProvider);
    }

    /**
     * Validate related links data
     *
     * @param array $dataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/products_related_multiple.php
     * @dataProvider getRelatedLinkProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     */
    public function testRelatedData(array $dataProvider) : void
    {
        $product = $this->productRepository->get('simple_with_cross');
        $this->validateLinkData($product, $dataProvider);
    }

    /**
     * Validate upsell links data
     *
     * @param array $dataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
     * @dataProvider getUpsellLinkProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     */
    public function testUpsellData(array $dataProvider) : void
    {
        $product = $this->productRepository->get('simple_with_upsell');
        $this->validateLinkData($product, $dataProvider);
    }

    /**
     * Validate grouped links data
     *
     * @param array $dataProvider
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @dataProvider getGroupedLinkProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     */
    public function testGroupedData(array $dataProvider) : void
    {
        $product = $this->productRepository->get('grouped');
        $this->validateLinkData($product, $dataProvider);
    }

    /**
     * Validate link data
     *
     * @param ProductInterface $product
     * @param array $dataProvider
     *
     * @return void
     * @throws \Throwable
     */
    private function validateLinkData(ProductInterface $product, array $dataProvider) : void
    {
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['links']);

        $catalogServiceItems = $this->catalogService->getProducts($this->productsGetRequestInterface)->getItems();

        $this->assertNotEmpty($catalogServiceItems);
        $item = \array_shift($catalogServiceItems);
        $actualData = [];

        /** @var \Magento\CatalogStorefrontApi\Api\Data\Link $link */
        foreach ($item->getLinks() as $link) {
            $actualData[] = $this->linkArrayMapper->convertToArray($link);
        }

        $this->compare($dataProvider, $actualData);
    }

    /**
     * Get crosssell link data provider
     *
     * @return array
     */
    public function getCrosssellLinkProvider() : array
    {
        return [
            'linkData' => [
                'item' => [
                    [
                        'position' => 1,
                        'type' => 'crosssell',
                        'qty' => 0.0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get upsell link data provider
     *
     * @return array
     */
    public function getUpsellLinkProvider() : array
    {
        return [
            'linkData' => [
                'item' => [
                    [
                        'product_id' => '1',
                        'position' => 1,
                        'type' => 'upsell',
                        'qty' => 0.0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get related link data provider
     *
     * @return array
     */
    public function getRelatedLinkProvider() : array
    {
        return [
            'linkData' => [
                'item' => [
                    [
                        'product_id' => '1',
                        'position' => 1,
                        'type' => 'related',
                        'qty' => 0.0,
                    ],
                    [
                        'product_id' => '3',
                        'position' => 1,
                        'type' => 'related',
                        'qty' => 0.0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get grouped link data provider
     *
     * @return array
     */
    public function getGroupedLinkProvider() : array
    {
        return [
            'linkData' => [
                'item' => [
                    [
                        'product_id' => '11',
                        'position' => 1,
                        'type' => 'associated',
                        'qty' => 1.0,
                    ],
                    [
                        'product_id' => '22',
                        'position' => 2,
                        'type' => 'associated',
                        'qty' => 1.0,
                    ],
                ],
            ],
        ];
    }
}
