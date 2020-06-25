<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExportApi\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Product entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Product extends AbstractModel implements ProductInterface
{
    private const ID = 'id';

    private const SKU = 'sku';

    private const PARENTS = 'parents';

    private const STORE_VIEW_CODE = 'store_view_code';

    private const STORE_CODE = 'store_code';

    private const WEBSITE_CODE = 'website_code';

    private const NAME = 'name';

    private const TYPE = 'type';

    private const META_DESCRIPTION = 'meta_description';

    private const META_KEYWORD = 'meta_keyword';

    private const META_TITLE = 'meta_title';

    private const STATUS = 'status';

    private const TAX_CLASS_ID = 'tax_class_id';

    private const CREATED_AT = 'created_at';

    private const UPDATED_AT = 'updated_at';

    private const URL_KEY = 'url_key';

    private const VISIBILITY = 'visibility';

    private const WEIGHT = 'weight';

    private const WEIGHT_UNIT = 'weight_unit';

    private const CURRENCY = 'currency';

    private const DISPLAYABLE = 'displayable';

    private const BUYABLE = 'buyable';

    private const ATTRIBUTES = 'attributes';

    private const CATEGORIES = 'categories';

    private const OPTIONS = 'options';

    private const ENTERED_OPTIONS = 'entered_options';

    private const IN_STOCK = 'in_stock';

    private const LOW_STOCK = 'low_stock';

    private const URL = 'url';

    private const ALLOW_OPEN_AMOUNT = 'allow_open_amount';

    private const GIFT_CARD_TYPE = 'gift_card_type';

    private const REDEEMABLE = 'redeemable';

    private const SKU_TYPE = 'sku_type';

    private const WEIGHT_TYPE = 'weight_type';

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku(string $sku)
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getParents(): ?array
    {
        return $this->getData(self::PARENTS);
    }

    /**
     * @inheritdoc
     */
    public function setParents(?array $parents): void
    {
        $this->setData(self::PARENTS, $parents);
    }

    /**
     * @inheritdoc
     */
    public function getStoreViewCode(): ?string
    {
        return $this->getData(self::STORE_VIEW_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setStoreViewCode($storeViewCode)
    {
        $this->setData(self::STORE_VIEW_CODE, $storeViewCode);
    }

    /**
     * @inheritdoc
     */
    public function getStoreCode(): ?string
    {
        return $this->getData(self::STORE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setStoreCode($storeCode)
    {
        $this->setData(self::STORE_CODE, $storeCode);
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteCode(): ?string
    {
        return $this->getData(self::WEBSITE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteCode($websiteCode)
    {
        $this->setData(self::WEBSITE_CODE, $websiteCode);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription($metaDescription)
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeyword(): ?string
    {
        return $this->getData(self::META_KEYWORD);
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->setData(self::META_KEYWORD, $metaKeyword);
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getTaxClassId(): ?string
    {
        return $this->getData(self::TAX_CLASS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTaxClassId($taxClassId)
    {
        $this->setData(self::TAX_CLASS_ID, $taxClassId);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritdoc
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setUrlKey($urlKey)
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @inheritdoc
     */
    public function getVisibility(): ?string
    {
        return $this->getData(self::VISIBILITY);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($visibility)
    {
        $this->setData(self::VISIBILITY, $visibility);
    }

    /**
     * @inheritdoc
     */
    public function getWeight(): ?int
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->setData(self::WEIGHT, $weight);
    }

    /**
     * @inheritdoc
     */
    public function getWeightUnit(): ?string
    {
        return $this->getData(self::WEIGHT_UNIT);
    }

    /**
     * @inheritdoc
     */
    public function setWeightUnit($weightUnit)
    {
        $this->setData(self::WEIGHT_UNIT, $weightUnit);
    }

    /**
     * @inheritdoc
     */
    public function getCurrency(): ?string
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritdoc
     */
    public function setCurrency($currency)
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayable(): ?bool
    {
        return $this->getData(self::DISPLAYABLE);
    }

    /**
     * @inheritdoc
     */
    public function setDisplayable($displayable)
    {
        $this->setData(self::DISPLAYABLE, $displayable);
    }

    /**
     * @inheritdoc
     */
    public function getBuyable(): ?bool
    {
        return $this->getData(self::BUYABLE);
    }

    /**
     * @inheritdoc
     */
    public function setBuyable($buyable)
    {
        $this->setData(self::BUYABLE, $buyable);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->getData(self::ATTRIBUTES);
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($attributes)
    {
        $this->setData(self::ATTRIBUTES, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }

    /**
     * @inheritdoc
     */
    public function setCategories($categories)
    {
        $this->setData(self::CATEGORIES, $categories);
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setOptions($options)
    {
        $this->setData(self::OPTIONS, $options);
    }

    /**
     * @inheritdoc
     */
    public function getEnteredOptions()
    {
        return $this->getData(self::ENTERED_OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setEnteredOptions($options)
    {
        $this->setData(self::ENTERED_OPTIONS, $options);
    }

    /**
     * @inheritdoc
     */
    public function getInStock(): ?bool
    {
        return $this->getData(self::IN_STOCK);
    }

    /**
     * @inheritdoc
     */
    public function setInStock($inStock)
    {
        $this->setData(self::IN_STOCK, $inStock);
    }

    /**
     * @inheritdoc
     */
    public function getLowStock(): ?bool
    {
        return $this->getData(self::LOW_STOCK);
    }

    /**
     * @inheritdoc
     */
    public function setLowStock($lowStock)
    {
        $this->setData(self::LOW_STOCK, $lowStock);
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): ?string
    {
        return $this->getData(self::URL);
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->setData(self::URL, $url);
    }

    /**
     * @inheritdoc
     */
    public function getAllowOpenAmount(): ?bool
    {
        return $this->getData(self::ALLOW_OPEN_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setAllowOpenAmount($allowOpenAmount)
    {
        $this->setData(self::ALLOW_OPEN_AMOUNT, $allowOpenAmount);
    }

    /**
     * @inheritdoc
     */
    public function getGiftCardType() :? string
    {
        return $this->getData(self::GIFT_CARD_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setGiftCardType($giftCardType)
    {
        $this->setData(self::GIFT_CARD_TYPE, $giftCardType);
    }

    /**
     * @inheritdoc
     */
    public function getRedeemable() :? bool
    {
        return $this->getData(self::REDEEMABLE);
    }

    /**
     * @inheritdoc
     */
    public function setRedeemable($redeemable)
    {
        $this->setData(self::REDEEMABLE, $redeemable);
    }

    /**
     * @inheritdoc
     */
    public function getSkuType() :? string
    {
        return $this->getData(self::SKU_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setSkuType($skuType)
    {
        $this->setData(self::SKU_TYPE, $skuType);
    }

    /**
     * @inheritdoc
     */
    public function getWeightType() :? string
    {
        return $this->getData(self::WEIGHT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setWeightType($weightType)
    {
        $this->setData(self::WEIGHT_TYPE, $weightType);
    }
}
