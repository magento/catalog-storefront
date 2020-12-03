<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\ProductVariants;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogStorefront\Model\VariantService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponseArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Framework\Exception\StateException;

/**
 * Test for Configurable ProductVariants storefront service
 */
class ConfigurableVariantsTest extends StorefrontTestsAbstract
{
    const ERROR_MESSAGE = 'No products variants for product with id %s are found in catalog.';

    /**
     * @var VariantService
     */
    private $variantService;

    /**
     * @var ProductVariantRequestInterface
     */
    private $variantsRequestInterface;

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
        $this->variantService = Bootstrap::getObjectManager()->create(VariantService::class);
        $this->variantsRequestInterface = Bootstrap::getObjectManager()->create(ProductVariantRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->responseArrayMapper = Bootstrap::getObjectManager()->create(
            ProductVariantResponseArrayMapper::class
        );
    }

    /**
     * Validate configurable product variants data
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_nine_simples.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testConfigurableProductVariants(): void
    {
        $simpleSkus = [
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
        /** @var $configurable Product */
        $configurable = $this->productRepository->get('configurable');
        $simples = [];
        foreach ($simpleSkus as $sku) {
            $simples[] = $this->productRepository->get($sku);
        }

        $this->variantsRequestInterface->setProductId((string)$configurable->getId());
        $this->variantsRequestInterface->setStore('default');

        //This sleep ensures that the elastic index has sufficient time to refresh
        //See https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-refresh.html#docs-refresh
        sleep(1);
        /** @var $variantServiceItem ProductVariantResponse */
        $variantServiceItem = $this->variantService->getProductVariants($this->variantsRequestInterface);
        $actual = $this->responseArrayMapper->convertToArray($variantServiceItem)['matched_variants'];

        $expected = $this->getExpectedProductVariants($configurable, $simples);
        self::assertCount(9, $actual);
        $this->compare($expected, $actual);

        $this->deleteProduct($configurable->getSku());
        $this->runConsumers(['catalog.product.variants.export.consumer']);

        //This sleep ensures that the elastic index has sufficient time to refresh
        //See https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-refresh.html#docs-refresh
        sleep(4);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(self::ERROR_MESSAGE, $configurable->getId()));
        $this->variantService->getProductVariants($this->variantsRequestInterface);
    }

    /**
     * Validate that only one variant is returned when one simple product out of two is disabled.
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testProductVariantsDisabledProduct(): void
    {
        /** @var $configurable Product */
        $configurable = $this->productRepository->get('configurable');

        $this->variantsRequestInterface->setProductId((string)$configurable->getId());
        $this->variantsRequestInterface->setStore('default');

        //This sleep ensures that the elastic index has sufficient time to refresh
        //See https://www.elastic.co/guide/en/elasticsearch/reference/6.8/docs-refresh.html#docs-refresh
        sleep(1);
        /** @var $variantServiceItem ProductVariantResponse */
        $variantServiceItem = $this->variantService->getProductVariants($this->variantsRequestInterface);
        $actual = $this->responseArrayMapper->convertToArray($variantServiceItem)['matched_variants'];
        self::assertCount(1, $actual);
    }

    /**
     * Get the expected variants for configurable products.
     *
     * @param Product $configurable
     * @param Product[] $simples
     * @return array
     */
    private function getExpectedProductVariants(Product $configurable, array $simples): array
    {
        $configurableOptions = $configurable->getExtensionAttributes()->getConfigurableProductOptions();
        $variants = [];
        foreach ($simples as $simple) {
            $id = (\sprintf(
                'configurable/%1$s/%2$s',
                $configurable->getId(),
                $simple->getId(),
            ));
            $optionValues = [];
            foreach ($configurableOptions as $configurableOption) {
                $attributeCode = $configurableOption->getProductAttribute()->getAttributeCode();
                foreach ($configurableOption->getValues() as $configurableOptionValue) {
                    if ($simple->getData($attributeCode) === $configurableOptionValue->getValueIndex()) {
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        $optionUid = \base64_encode(\sprintf(
                            'configurable/%1$s/%2$s',
                            $configurableOption->getAttributeId(),
                            $configurableOptionValue->getValueIndex()
                        ));
                        $optionValues[] = \sprintf(
                            '%1$s:%2$s/%3$s',
                            $configurable->getId(),
                            $attributeCode,
                            $optionUid
                        );
                    }
                }
            }
            $variants[$id] = [
                'id' => $id,
                'option_values' => $optionValues,
                'product_id' => $simple->getId(),
            ];
        }
        return array_values($variants);
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
