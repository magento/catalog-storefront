<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

interface ProductInterface
{
    /**
     * Get product ID
     *
     * @return ?int
     */
    public function getId(): ?int;

    /**
     * Set product ID
     *
     * @param ?int $id
     * @return void
     */
    public function setId($id);

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getSku() : ?string;

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku);

    /**
     * Get parent products
     *
     * @return int[]|null
     */
    public function getParents(): ?array;

    /**
     * Set parent products
     *
     * @param int[]|null $parents
     * @return void
     */
    public function setParents(?array $parents): void;

    /**
     * Get product store view code
     *
     * @return string
     */
    public function getStoreViewCode() : ?string;

    /**
     * Set product store view code
     *
     * @param string $storeViewCode
     * @return void
     */
    public function setStoreViewCode($storeViewCode);

    /**
     * Get product store code
     *
     * @return string
     * @return void
     */
    public function getStoreCode() : ?string;

    /**
     * Set product store code
     *
     * @param string $storeCode
     * @return void
     */
    public function setStoreCode($storeCode);

    /**
     * Get product website code
     *
     * @return string
     */
    public function getWebsiteCode() : ?string;

    /**
     * Set product website code
     *
     * @param string $websiteCode
     * @return void
     */
    public function setWebsiteCode($websiteCode);

    /**
     * Get product name
     *
     * @return string
     */
    public function getName() : ?string;

    /**
     * Set product name
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Get product type
     *
     * @return string
     */
    public function getType() : ?string;

    /**
     * Set product type
     *
     * @param string $type
     * @return void
     */
    public function setType($type);

    /**
     * Get product meta description
     *
     * @return string
     */
    public function getMetaDescription() : ?string;

    /**
     * Set product meta description
     *
     * @param string $metaDescription
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get product meta keyword
     *
     * @return string
     */
    public function getMetaKeyword() : ?string;

    /**
     * Set product meta keyword
     *
     * @param string $metaKeyword
     * @return void
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * Get product meta title
     *
     * @return string
     */
    public function getMetaTitle() : ?string;

    /**
     * Set product meta title
     *
     * @param string $metaTitle
     * @return void
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get product status
     *
     * @return string
     */
    public function getStatus() : ?string;

    /**
     * Set product status
     *
     * @param string $status
     * @return void
     */
    public function setStatus($status);

    /**
     * Get product tax class ID
     *
     * @return string
     */
    public function getTaxClassId() : ?string;

    /**
     * Set product tax class ID
     *
     * @param string $taxClassId
     * @return void
     */
    public function setTaxClassId($taxClassId);

    /**
     * Get product created at
     *
     * @return string
     */
    public function getCreatedAt() : ?string;

    /**
     * Set product created at
     *
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * Get product updated at
     *
     * @return string
     */
    public function getUpdatedAt() : ?string;

    /**
     * Set product updated at
     *
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get product URL key
     *
     * @return string
     */
    public function getUrlKey() : ?string;

    /**
     * Set product URL key
     *
     * @param string $urlKey
     * @return void
     */
    public function setUrlKey($urlKey);

    /**
     * Get product visibility
     *
     * @return string
     */
    public function getVisibility() : ?string;

    /**
     * Set product visibility
     *
     * @param string $visibility
     * @return void
     */
    public function setVisibility($visibility);

    /**
     * Get product weight
     *
     * @return int
     */
    public function getWeight() : ?int;

    /**
     * Set product weight
     *
     * @param int $weight
     * @return void
     */
    public function setWeight($weight);

    /**
     * Get product weight unit
     *
     * @return string
     */
    public function getWeightUnit() : ?string;

    /**
     * Set product weight unit
     *
     * @param string $weightUnit
     * @return void
     */
    public function setWeightUnit($weightUnit);

    /**
     * Get product currency
     *
     * @return string
     */
    public function getCurrency() : ?string;

    /**
     * Set product currency
     *
     * @param string $currency
     * @return void
     */
    public function setCurrency($currency);

    /**
     * Get is product displayable
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplayable() : ?bool;

    /**
     * Set is product displayable
     *
     * @param bool $displayable
     * @return void
     */
    public function setDisplayable($displayable);

    /**
     * Get is allowed to buy product
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getBuyable() : ?bool;

    /**
     * Set is allowed to buy product
     *
     * @param bool $buyable
     * @return void
     */
    public function setBuyable($buyable);

    /**
     * Get product attributes
     *
     * @return \Magento\CatalogExportApi\Api\Data\AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Set product attributes
     *
     * @param \Magento\CatalogExportApi\Api\Data\AttributeInterface[] $attributes
     * @return void
     */
    public function setAttributes($attributes);

    /**
     * Get product categories
     *
     * @return string[]
     */
    public function getCategories();

    /**
     * Set product categories
     *
     * @param string[] $categories
     * @return void
     */
    public function setCategories($categories);

    /**
     * Get product options
     *
     * @return \Magento\CatalogExportApi\Api\Data\CustomOption[]
     */
    public function getOptions();

    /**
     * Set product options
     *
     * @param \Magento\CatalogExportApi\Api\Data\CustomOption[] $options
     * @return void
     */
    public function setOptions($options);

    /**
     * Get product options
     *
     * @return \Magento\CatalogExportApi\Api\Data\EnteredOption[]
     */
    public function getEnteredOptions();

    /**
     * Set product options
     *
     * @param \Magento\CatalogExportApi\Api\Data\EnteredOption[] $options
     * @return void
     */
    public function setEnteredOptions($options);

    /**
     * Get media gallery
     *
     * @return \Magento\CatalogExportApi\Api\Data\GalleryItem[]|null
     */
    public function getMediaGallery() : ?array;

    /**
     * Set media gallery
     *
     * @param \Magento\CatalogExportApi\Api\Data\GalleryItem[]|null $mediaGallery
     *
     * @return void
     */
    public function setMediaGallery(?array $mediaGallery) : void;

    /**
     * Get is product in stock
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getInStock() : ?bool;

    /**
     * Set is product in stock
     *
     * @param bool $inStock
     * @return void
     */
    public function setInStock($inStock);

    /**
     * Get product low stock
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLowStock() : ?bool;

    /**
     * Set product low stock
     *
     * @param bool $lowStock
     * @return void
     */
    public function setLowStock($lowStock);

    /**
     * Get product URL
     *
     * @return string
     */
    public function getUrl() : ?string;

    /**
     * Set product URL
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * Get is allowed open amount
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAllowOpenAmount() :? bool;

    /**
     * Set is allowed open amount
     *
     * @param bool $allowOpenAmount
     * @return void
     */
    public function setAllowOpenAmount($allowOpenAmount);

    /**
     * Get gift card type
     *
     * @return string
     */
    public function getGiftCardType() :? string;

    /**
     * Set gift card type
     *
     * @param bool $giftCardType
     * @return void
     */
    public function setGiftCardType($giftCardType);

    /**
     * Get is gift card redeemable
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRedeemable() :? bool;

    /**
     * Set is gift card redeemable
     *
     * @param bool $redeemable
     * @return void
     */
    public function setRedeemable($redeemable);

    /**
     * Get product SKU type
     *
     * @return string
     */
    public function getSkuType() :? string;

    /**
     * Set product SKU type
     *
     * @param string $skuType
     * @return void
     */
    public function setSkuType($skuType);

    /**
     * Get product weight type
     *
     * @return string
     */
    public function getWeightType() :? string;

    /**
     * Set product weight type
     *
     * @param string $weightType
     * @return void
     */
    public function setWeightType($weightType);
}
