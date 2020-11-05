<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\ProductVariants\ConfigurableTest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogStorefront\Model\VariantService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponseArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests configurable product variants on storefront
 */
class ConfigurableVariantsTest extends StorefrontTestsAbstract
{
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testConfigurableProductVariants(): void
    {
        /** @var $configurable Product */
        $configurable = $this->productRepository->get('configurable');
        $simples = [
            $this->productRepository->get('simple_10'),
            $this->productRepository->get('simple_20')
        ];

        $this->variantsRequestInterface->setProductId((string)$configurable->getId());
        $this->variantsRequestInterface->setStore('default');

        /** @var $variantServiceItem ProductVariantResponse */
        $variantServiceItem = $this->variantService->GetProductVariants($this->variantsRequestInterface);
        $actual = $this->responseArrayMapper->convertToArray($variantServiceItem)['matched_variants'];

        $expected = $this->getExpectedProductVariants($configurable, $simples);
        self::assertCount(2, $actual);
        $this->compare($expected, $actual);
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

        /** @var $variantServiceItem ProductVariantResponse */
        $variantServiceItem = $this->variantService->GetProductVariants($this->variantsRequestInterface);
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
}
