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
use Magento\CatalogStorefrontApi\Api\Data\ProductOption;
use Magento\CatalogStorefrontApi\Api\Data\ProductOptionArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductShopperInputOption;
use Magento\CatalogStorefrontApi\Api\Data\ProductShopperInputOptionArrayMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests gift card product options on the storefront
 */
class GiftCardProductTest extends StorefrontTestsAbstract
{
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
        $this->optionArrayMapper = Bootstrap::getObjectManager()->create(ProductOptionArrayMapper::class);
        $this->shopperInputOptionArrayMapper = Bootstrap::getObjectManager()->create(
            ProductShopperInputOptionArrayMapper::class
        );
    }

    /**
     * Validate physical gift card product data
     *
     * @magentoApiDataFixture Magento/GiftCard/_files/gift_card_physical_with_fixed_amount_10.php
     * @magentoDbIsolation disabled
     * @param array $expectedOptions
     * @param array $expectedShopperInputOptions
     * @dataProvider getGiftCardProductProvider
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testPhysicalGiftCard(array $expectedOptions, array $expectedShopperInputOptions): void
    {
        $product = $this->productRepository->get('gift-card-with-fixed-amount-10');
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore('default');
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);

        self::assertNotEmpty($catalogServiceItem->getItems());

        $actualOptions = $this->getOptions($catalogServiceItem);
        $this->compare($expectedOptions, $actualOptions);
        $actualShopperInputOptions = $this->getInputOptions($catalogServiceItem);
        $this->compare($expectedShopperInputOptions, $actualShopperInputOptions);
    }

    /**
     * Validate virtual gift card product data in multiple websites
     *
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/GiftCard/_files/gift_card_with_amount_multiple_websites.php
     * @magentoConfigFixture default_store giftcard/general/allow_message 1
     * @magentoConfigFixture default_store giftcard/general/message_max_length 255
     * @magentoDbIsolation disabled
     * @param array $defaultWebsiteOptions
     * @param array $defaultWebsiteInputOptions
     * @param array $secondWebsiteOptions
     * @param array $secondWebsiteInputOptions
     * @throws NoSuchEntityException
     * @throws \Throwable
     * @dataProvider getVirtualMultiWebsiteDataProvider
     */
    public function testVirtualGiftCardMultiWebsite(
        array $defaultWebsiteOptions,
        array $defaultWebsiteInputOptions,
        array $secondWebsiteOptions,
        array $secondWebsiteInputOptions
    ): void {
        $product = $this->productRepository->get('gift-card-with-amount');
        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore('default');
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());
        $actualOptions = $this->getOptions($catalogServiceItem);
        $this->compare($defaultWebsiteOptions, $actualOptions);
        $actualShopperInputOptions = $this->getInputOptions($catalogServiceItem);
        $this->compare($defaultWebsiteInputOptions, $actualShopperInputOptions);

        $this->productsGetRequestInterface->setStore('fixture_second_store');
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());
        $actualOptions = $this->getOptions($catalogServiceItem);
        $this->compare($secondWebsiteOptions, $actualOptions);
        $actualShopperInputOptions = $this->getInputOptions($catalogServiceItem);
        $this->compare($secondWebsiteInputOptions, $actualShopperInputOptions);
    }

    /**
     * Get options array
     *
     * @param ProductsGetResultInterface $catalogServiceItem
     * @return array
     */
    private function getOptions(ProductsGetResultInterface $catalogServiceItem): array
    {
        $options = [];
        foreach ($catalogServiceItem->getItems()[0]->getProductOptions() as $option) {
            /**
             * @var $option ProductOption
             */
            $convertedValues = $this->optionArrayMapper->convertToArray($option);
            $options[] = $convertedValues;
        }
        return $options;
    }

    /**
     * Get shopper input options array
     *
     * @param ProductsGetResultInterface $catalogServiceItem
     * @return array
     */
    private function getInputOptions(ProductsGetResultInterface $catalogServiceItem): array
    {
        $shopperInputOptions = [];
        foreach ($catalogServiceItem->getItems()[0]->getShopperInputOptions() as $option) {
            /**
             * @var $option ProductShopperInputOption
             */
            $convertedValues = $this->shopperInputOptionArrayMapper->convertToArray($option);
            $shopperInputOptions[] = $convertedValues;
        }
        return $shopperInputOptions;
    }

    /**
     * Data provider for gift card product options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getGiftCardProductProvider(): array
    {
        return [
            [
                'productOptions' => [
                    [
                        'label' => 'Amount',
                        'render_type' => 'drop_down',
                        'type' => 'giftcard',
                        'values' => [
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            ['id' => \base64_encode('giftcard/giftcard_amount/10.0000')]
                        ]
                    ]
                ],
                'productShopperInputOptions' => [
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_sender_name'),
                        'label' => 'Sender Name',
                        'render_type' => 'text'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_recipient_name'),
                        'label' => 'Recipient Name',
                        'render_type' => 'text'
                    ]
                ]
            ]
        ];
    }

    /**
     * Virtual gift card with fixed and open amount data provider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getVirtualMultiWebsiteDataProvider(): array
    {
        return [
            [
                'defaultWebsiteOptions' => [
                    [
                        'label' => 'Amount',
                        'render_type' => 'drop_down',
                        'type' => 'giftcard',
                        'values' => [
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            ['id' => \base64_encode('giftcard/giftcard_amount/7.0000')],
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            ['id' => \base64_encode('giftcard/giftcard_amount/17.0000')]
                        ]
                    ]
                ],
                'defaultWebsiteInputOptions' => [
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_sender_name'),
                        'label' => 'Sender Name',
                        'render_type' => 'text'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_recipient_name'),
                        'label' => 'Recipient Name',
                        'render_type' => 'text'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_sender_email'),
                        'label' => 'Sender Email',
                        'render_type' => 'email'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_recipient_email'),
                        'label' => 'Recipient Email',
                        'render_type' => 'email'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_message'),
                        'label' => 'Message',
                        'render_type' => 'text',
                        'range' => [
                            'from' => 0.0,
                            'to' => 255.0
                        ]
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/custom_giftcard_amount'),
                        'label' => 'Amount in',
                        'render_type' => 'giftcardopenamount',
                        'range' => [
                            'from' => 100.0,
                            'to' => 150.0
                        ]
                    ]
                ],
                'secondWebsiteOptions' => [
                    [
                        'label' => 'Amount',
                        'render_type' => 'drop_down',
                        'type' => 'giftcard',
                        'values' => [
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            ['id' => \base64_encode('giftcard/giftcard_amount/7.0000')],
                            // phpcs:ignore Magento2.Functions.DiscouragedFunction
                            ['id' => \base64_encode('giftcard/giftcard_amount/17.0000')]
                        ]
                    ]
                ],
                'secondWebsiteInputOptions' => [
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_sender_name'),
                        'label' => 'Sender Name',
                        'render_type' => 'text'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_recipient_name'),
                        'label' => 'Recipient Name',
                        'render_type' => 'text'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_sender_email'),
                        'label' => 'Sender Email',
                        'render_type' => 'email'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_recipient_email'),
                        'label' => 'Recipient Email',
                        'render_type' => 'email'
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/giftcard_message'),
                        'label' => 'Message',
                        'render_type' => 'text',
                        'range' => [
                            'from' => 0.0,
                            'to' => 255.0
                        ]
                    ],
                    [
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'id' => \base64_encode('giftcard/custom_giftcard_amount'),
                        'label' => 'Amount in',
                        'render_type' => 'giftcardopenamount',
                        'range' => [
                            'from' => 100.0,
                            'to' => 150.0
                        ]
                    ]
                ]
            ]
        ];
    }
}
