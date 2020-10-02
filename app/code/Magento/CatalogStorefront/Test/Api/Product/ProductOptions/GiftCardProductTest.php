<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\ProductOptions;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductShopperInputOptionArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests gift card product options on the storefront
 */
class GiftCardProductTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'gift-card-with-fixed-amount-10';
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'product_options',
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
    private $productRepository;

    /**
     * @var ProductOptionArrayMapper
     */
    private $optionArrayMapper;

    /**
     * @var ProductShopperInputOptionArrayMapper
     */
    private $shopperInputOptionArrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->optionArrayMapper = Bootstrap::getObjectManager()->create(ProductOptionArrayMapper::class);
        $this->shopperInputOptionArrayMapper = Bootstrap::getObjectManager()->create(
            ProductShopperInputOptionArrayMapper::class
        );
    }

    /**
     * Validate gift card product data
     *
     * @magentoApiDataFixture Magento/GiftCard/_files/gift_card_physical_with_fixed_amount_10.php
     * @magentoConfigFixture default_store giftcard/general/allow_message 1
     * @magentoConfigFixture default_store giftcard/general/message_max_length 266
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getGiftCardProductProvider
     */
    public function testGiftCardProduct(array $expectedOptions, array $expectedShopperInputOptions)
    {
        $product = $this->productRepository->get(self::TEST_SKU);
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());

        $actualOptions = [];
        foreach ($catalogServiceItem->getItems()[0]->getProductOptions() as $option) {
            $convertedValues = $this->optionArrayMapper->convertToArray($option);
            $actualOptions[] = $convertedValues;
        }
        $this->compare($expectedOptions, $actualOptions);

        $actualShopperInputOptions = [];
        foreach ($catalogServiceItem->getItems()[0]->getShopperInputOptions() as $option) {
            $convertedValues = $this->shopperInputOptionArrayMapper->convertToArray($option);
            $actualShopperInputOptions[] = $convertedValues;
        }
        $this->compare($expectedShopperInputOptions, $actualShopperInputOptions);
    }

    /**
     * Data provider for gift card product options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getGiftCardProductProvider()
    {
        return [
                [
                    'productOptions' => [
                        [
                            'type' => 'giftcard',
                            'values' => [
                                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                                ['id' => \base64_encode('giftcard/giftcard_amount/10.0000')]
                            ]
                        ]
                    ],
                    'productShopperInputOptions' => [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        ['id' => \base64_encode('giftcard/giftcard_recipient_name')],
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        ['id' => \base64_encode('giftcard/giftcard_sender_name')],
                        [
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            'id' => \base64_encode('giftcard/giftcard_message'),
                            'range' => [
                                'from' => 0.0,
                                'to' => 266.0
                            ]
                        ]
                    ]
                ]
        ];
    }
}
