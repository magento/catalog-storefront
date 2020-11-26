<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\ProductVariants\ProductVariantsConsumer;
use Magento\CatalogStorefront\Model\VariantService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponseArrayMapper;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\ProductVariantDataExporter\Model\ProductVariantFeedInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Products variants message bus
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductVariantsTest extends StorefrontTestsAbstract
{
    const CONFIGURABLE_SKU = 'configurable';
    const SIMPLE_SKUS = [
        'simple_0',
        'simple_1',
        'simple_2',
        'simple_3',
        'simple_4',
        'simple_5',
        'simple_6',
        'simple_7',
        'simple_8'
    ];

    const STORE_CODE = 'default';
    const ERROR_MESSAGE = 'No products variants for product with id %s are found in catalog.';

    /**
     * @var ProductVariantsConsumer
     */
    private $productVariantsConsumer;

    /**
     * @var VariantService
     */
    private $variantService;

    /**
     * @var ProductVariantRequestInterface
     */
    private $variantsGetRequestInterface;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var ProductVariantFeedInterface
     */
    private $productVariantFeed;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductVariantResponseArrayMapper
     */
    private $responseArrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productVariantsConsumer = Bootstrap::getObjectManager()->create(ProductVariantsConsumer::class);
        $this->variantService = Bootstrap::getObjectManager()->create(VariantService::class);
        $this->variantsGetRequestInterface = Bootstrap::getObjectManager()->create(
            ProductVariantRequestInterface::class
        );
        $this->messageBuilder = Bootstrap::getObjectManager()->create(ChangedEntitiesMessageBuilder::class);
        $this->productVariantFeed = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed('variants');
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->responseArrayMapper = Bootstrap::getObjectManager()->create(ProductVariantResponseArrayMapper::class);
    }

    /**
     * Validate save and delete product variant operations
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_nine_simples.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Throwable
     */
    public function testSaveAndDeleteProductVariant(): void
    {
        $configurable = $this->getProduct(self::CONFIGURABLE_SKU);
        $configurableId = $configurable->getId();

        $entitiesData = [];
        foreach (self::SIMPLE_SKUS as $simpleSku) {
            $simpleId = $this->getProduct($simpleSku)->getId();
            $entitiesData[]['entity_id'] = \sprintf('configurable/%1$s/%2$s', $configurableId, $simpleId);
        }

        $productVariantFeed = $this->productVariantFeed->getFeedByProductIds([$configurableId]);
        $this->assertNotEmpty($productVariantFeed['feed']);
        $this->assertCount(9, $productVariantFeed['feed']);
        $expectedData = $this->formatFeedData($productVariantFeed['feed']);

        $updateMessage = $this->messageBuilder->build(
            'product_variants_updated',
            $entitiesData
        );
        $this->productVariantsConsumer->processMessage($updateMessage);

        $this->variantsGetRequestInterface->setProductId((string)$configurableId);
        $this->variantsGetRequestInterface->setStore(self::STORE_CODE);
        //This sleep ensures that the elastic index has sufficient time to refresh
        //See https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-refresh.html#docs-refresh
        sleep(1);
        $response = $this->variantService->getProductVariants($this->variantsGetRequestInterface);
        $variants = $this->responseArrayMapper->convertToArray($response);

        $this->assertNotEmpty($variants);
        $this->compare($expectedData, $variants['matched_variants']);

        $this->deleteProduct($configurable->getSku());
        $deletedFeed = $this->productVariantFeed->getDeletedByProductIds([$configurableId]);
        $this->assertNotEmpty($deletedFeed);

        $deleteMessage = $this->messageBuilder->build(
            'product_variants_deleted',
            $entitiesData
        );
        $this->productVariantsConsumer->processMessage($deleteMessage);
        //This sleep ensures that the elastic index has sufficient time to refresh
        //See https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-refresh.html#docs-refresh
        sleep(4);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(self::ERROR_MESSAGE, $configurableId));
        $this->variantService->getProductVariants($this->variantsGetRequestInterface);
    }

    /**
     * Transform variant feed data in catalog storefront format
     *
     * @param array $feedData
     * @return array|void
     */
    private function formatFeedData(array $feedData)
    {
        try {
            return \array_map(function (array $feedItem) {
                $childId = \explode('/', $feedItem['id'])[2];
                $feedItem['product_id'] = $childId;
                unset($feedItem['modifiedAt'], $feedItem['deleted'], $feedItem['parent_id']);
                return $feedItem;
            }, $feedData);
        } catch (\Exception $e) {
            $this->fail('Feed data did not match the expected format');
        }
    }

    /**
     * Get product
     *
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProduct(string $sku): ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Could not retrieve product with sku ' . $sku));
        }
    }

    /**
     * Delete product
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
            throw new NoSuchEntityException(__('Could not delete product with sku ' . $sku));
        }
    }
}
