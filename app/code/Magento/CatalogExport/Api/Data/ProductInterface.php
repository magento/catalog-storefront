<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

interface ProductInterface
{
    /**
     * @return string|null
     */
    public function getId();

    /**
     * @param string|null $id
     * @return void
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getSku() : ?string;

    /**
     * @param string $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * @return int[]
     */
    public function getParents();

    /**
     * @param int[] $parents
     * @return void
     */
    public function setParents($parents);

    /**
     * @return string
     */
    public function getStoreViewCode() : ?string;

    /**
     * @param string $storeViewCode
     * @return void
     */
    public function setStoreViewCode($storeViewCode);

    /**
     * @return string
     * @return void
     */
    public function getStoreCode() : ?string;

    /**
     * @param string $storeCode
     * @return void
     */
    public function setStoreCode($storeCode);

    /**
     * @return string
     */
    public function getWebsiteCode() : ?string;

    /**
     * @param string $websiteCode
     * @return void
     */
    public function setWebsiteCode($websiteCode);

    /**
     * @return string
     */
    public function getName() : ?string;

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getType() : ?string;

    /**
     * @param string $type
     * @return void
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getMetaDescription() : ?string;

    /**
     * @param string $metaDescription
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getMetaKeyword() : ?string;

    /**
     * @param string $metaKeyword
     * @return void
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * @return string
     */
    public function getMetaTitle() : ?string;

    /**
     * @param string $metaTitle
     * @return void
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string
     */
    public function getStatus() : ?string;

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getTaxClassId() : ?string;

    /**
     * @param string $taxClassId
     * @return void
     */
    public function setTaxClassId($taxClassId);

    /**
     * @return string
     */
    public function getCreatedAt() : ?string;

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt() : ?string;

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getUrlKey() : ?string;

    /**
     * @param string $urlKey
     * @return void
     */
    public function setUrlKey($urlKey);

    /**
     * @return string
     */
    public function getVisibility() : ?string;

    /**
     * @param string $visibility
     * @return void
     */
    public function setVisibility($visibility);

    /**
     * @return int
     */
    public function getWeight() : ?int;

    /**
     * @param int $weight
     * @return void
     */
    public function setWeight($weight);

    /**
     * @return string
     */
    public function getWeightUnit() : ?string;

    /**
     * @param string $weightUnit
     * @return void
     */
    public function setWeightUnit($weightUnit);

    /**
     * @return string
     */
    public function getCurrency() : ?string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency($currency);

    /**
     * @return bool
     */
    public function getDisplayable() : ?bool;

    /**
     * @param bool $displayable
     * @return void
     */
    public function setDisplayable($displayable);

    /**
     * @return bool
     */
    public function getBuyable() : ?bool;

    /**
     * @param bool $buyable
     * @return void
     */
    public function setBuyable($buyable);

    /**
     * @return \Magento\CatalogExport\Api\Data\AttributeInterface[]
     */
    public function getAttributes();

    /**
     * @param \Magento\CatalogExport\Api\Data\AttributeInterface[] $attributes
     * @return void
     */
    public function setAttributes($attributes);

    /**
     * @return string[]
     */
    public function getCategories();

    /**
     * @param string[] $categories
     * @return void
     */
    public function setCategories($categories);

    /**
     * @return string[]
     */
    public function getOptions();

    /**
     * @param string[] $options
     * @return void
     */
    public function setOptions($options);

    /**
     * @return bool
     */
    public function getInStock() : ?bool;

    /**
     * @param bool $inStock
     * @return void
     */
    public function setInStock($inStock);

    /**
     * @return bool
     */
    public function getLowStock() : ?bool;

    /**
     * @param bool $lowStock
     * @return void
     */
    public function setLowStock($lowStock);

    /**
     * @return string
     */
    public function getUrl() : ?string;

    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * @return bool
     */
    public function getAllowOpenAmount() :? bool;

    /**
     * @param bool $allowOpenAmount
     * @return void
     */
    public function setAllowOpenAmount($allowOpenAmount);

    /**
     * @return bool
     */
    public function getGiftCardType() :? string;

    /**
     * @param bool $giftCardType
     * @return void
     */
    public function setGiftCardType($giftCardType);

    /**
     * @return bool
     */
    public function getRedeemable() :? bool;

    /**
     * @param bool $redeemable
     * @return void
     */
    public function setRedeemable($redeemable);

    /**
     * @return string
     */
    public function getSkuType() :? string;

    /**
     * @param string $skuType
     * @return void
     */
    public function setSkuType($skuType);

    /**
     * @return string
     */
    public function getWeightType() :? string;

    /**
     * @param string $weightType
     * @return void
     */
    public function setWeightType($weightType);
}