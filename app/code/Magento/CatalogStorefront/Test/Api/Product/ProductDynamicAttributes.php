<?php

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
///Users/ledian/projects/storefront/repos/magento2ce/dev/tests/integration/testsuite/Magento/Eav/_files/attribute_for_search.php
class ProductDynamicAttributes extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'downloadable-product';
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'product_options',
        'links_purchased_separately'
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
     * @var ProductOptionArrayMapper
     */
    protected $arrayMapper;

    /**
     * @var CompareArraysRecursively
     */
    protected $compareArraysRecursively;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(ProductOptionArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Test product shopper input options
     *
     * @magentoApiDataFixture Magento/Eav/_files/attribute_for_search.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @dataProvider shopperInputOptionProvider
     */
    public function testEavSearchAttribute()
    {
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());
    }
}
