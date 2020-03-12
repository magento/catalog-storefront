<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogExtractor\DataProvider\Product\Price\CompositeProducts;
use Magento\CatalogExtractor\DataProvider\Product\Price\SimpleProducts;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class PriceRange implements DataProviderInterface
{
    /**
     * @var Discount
     */
    private $discount;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Price\CompositeProducts
     */
    private $compositeProductPrices;
    /**
     * @var SimpleProducts
     */
    private $simpleProducts;

    /**
     * @param Discount $discount
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param CompositeProducts $compositeProductPrices
     * @param SimpleProducts $simpleProducts
     */
    public function __construct(
        Discount $discount,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CompositeProducts $compositeProductPrices,
        SimpleProducts $simpleProducts
    ) {
        $this->discount = $discount;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->compositeProductPrices = $compositeProductPrices;
        $this->simpleProducts = $simpleProducts;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $output = [];
        $storeId = (int)$scopes['store'];
        $customerGroupId = (int)$scopes['customer_group'];
        $store = $this->storeManager->getStore($storeId);

        $simpleProductPrices = $this->simpleProducts->getPrices(
            $productIds,
            $customerGroupId,
            (int)$store->getWebsiteId()
        );

        $compositeProductPrices = $this->compositeProductPrices->getPrices(
            $productIds,
            $customerGroupId,
            (int)$store->getWebsiteId()
        );

        $productPrices = \array_merge($simpleProductPrices, $compositeProductPrices);

        $products = $this->productCollectionFactory->create()
            ->setStoreId($storeId)
            ->addPriceData($customerGroupId, $store->getWebsiteId())
            ->addIdFilter($productIds);

        /** @var Product $product */
        foreach ($products as $product) {
            $product->setPriceCalculation(true);
            $productId = $product->getId();
            $output[$productId]['price_range']['minimum_price'] = $this->formatPrice(
                (float)($productPrices[$productId]['regular_min_price'] ?? $this->getRegularPrice($product)),
                $this->getFinalMinPrice($product),
                $store
            );
            $output[$productId]['price_range']['maximum_price'] = $this->formatPrice(
                (float)($productPrices[$productId]['regular_max_price'] ?? $this->getRegularPrice($product)),
                $this->getFinalMaxPrice($product),
                $store
            );

            // ad-hoc solution for price fixed product taxes
            $output[$productId]['price_range']['minimum_price']['id'] = $productId;
            $output[$productId]['price_range']['maximum_price']['id'] = $productId;
        }

        return $output;
    }

    private function getRegularPrice(Product $product)
    {
        $finalPrice =  $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE);
        return (float)$finalPrice->getAmount()->getValue();
    }

    private function getFinalPrice(Product $product)
    {
        $finalPrice =  $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        return (float)$finalPrice->getMinimalPrice()->getValue();
    }

    private function getFinalMinPrice(Product $product)
    {
        return (float)($product->isComposite() ? $product->getMinPrice() : $this->getFinalPrice($product));
    }

    private function getFinalMaxPrice(Product $product)
    {
        return (float)($product->isComposite() ? $product->getMaxPrice() : $this->getFinalPrice($product));
    }

    /**
     * Format price for GraphQl output
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @param StoreInterface $store
     * @return array
     */
    private function formatPrice(float $regularPrice, float $finalPrice, StoreInterface $store): array
    {
        return [
            'regular_price' => [
                'value' => $regularPrice,
                'currency' => $store->getCurrentCurrencyCode()
            ],
            'final_price' => [
                'value' => $finalPrice,
                'currency' => $store->getCurrentCurrencyCode()
            ],
            'discount' => $this->discount->getDiscountByDifference($regularPrice, $finalPrice),
        ];
    }
}