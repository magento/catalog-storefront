<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\StorefrontTestsAbstract;

/**
 * Tests simple product on the store front
 * @magentoAppIsolation enabled
 */
class SimpleProductStoreFrontTest extends StorefrontTestsAbstract
{
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'shopper_input_options'
    ];

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
     * @var FeedInterface
     */
    private $productFeed;

    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->productFeed = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed('products');
        $this->json = Bootstrap::getObjectManager()->get(Json::class);
    }

    /**
     * Test product export REST API
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     * @throws \Throwable
     */
    public function testStoreFrontData(): void
    {
        $product = $this->productRepository->get('simple');

        $productFeed = $this->productFeed->getFeedByIds([(int)$product->getId()], [self::STORE_CODE]);
        $this->assertNotEmpty($productFeed);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);

        $this->assertNotEmpty($catalogServiceItem->getItems());
        $item = $catalogServiceItem->getItems()[0];

        $this->assertEquals(
            count($item->getShopperInputOptions()),
            count($productFeed['feed'][0]['productShopperInputOptions'])
        );
    }
}
