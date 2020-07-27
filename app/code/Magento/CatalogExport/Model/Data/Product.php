<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExportApi\Api\Data\ProductImage;
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

    private const MEDIA_GALLERY = 'media_gallery';

    private const IMAGE = 'image';

    private const SMALL_IMAGE = 'small_image';

    private const THUMBNAIL = 'thumbnail';

    private const SWATCH_IMAGE = 'swatch_image';

    private const IN_STOCK = 'in_stock';

    private const LOW_STOCK = 'low_stock';

    private const URL = 'url';

    private const ALLOW_OPEN_AMOUNT = 'allow_open_amount';

    private const GIFT_CARD_TYPE = 'gift_card_type';

    private const REDEEMABLE = 'redeemable';

    private const SKU_TYPE = 'sku_type';

    private const WEIGHT_TYPE = 'weight_type';

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id
     *
     * @param mixed $id
     * @return AbstractModel|void
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
    }

    /**
     * Get sku
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set sku
     *
     * @param string $sku
     */
    public function setSku(string $sku)
    {
        $this->setData(self::SKU, $sku);
    }


    /**
     * Get parents
     *
     * @return array|null
     */
    public function getParents(): ?array
    {
        return $this->getData(self::PARENTS);
    }


    /**
     * Set parents
     *
     * @param array|null $parents
     */
    public function setParents(?array $parents): void
    {
        $this->setData(self::PARENTS, $parents);
    }


    /**
     * Get store view code
     *
     * @return string|null
     */
    public function getStoreViewCode(): ?string
    {
        return $this->getData(self::STORE_VIEW_CODE);
    }


    /**
     * Set store view code
     *
     * @param string $storeViewCode
     */
    public function setStoreViewCode($storeViewCode)
    {
        $this->setData(self::STORE_VIEW_CODE, $storeViewCode);
    }

    /**
     * Get store code
     *
     * @return string|null
     */
    public function getStoreCode(): ?string
    {
        return $this->getData(self::STORE_CODE);
    }


    /**
     * Set store code
     *
     * @param string $storeCode
     */
    public function setStoreCode($storeCode)
    {
        $this->setData(self::STORE_CODE, $storeCode);
    }

    /**
     * Get website code
     *
     * @return string|null
     */
    public function getWebsiteCode(): ?string
    {
        return $this->getData(self::WEBSITE_CODE);
    }

    /**
     * Set website code
     *
     * @param string $websiteCode
     */
    public function setWebsiteCode($websiteCode)
    {
        $this->setData(self::WEBSITE_CODE, $websiteCode);
    }

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get meta keyword
     *
     * @return string|null
     */
    public function getMetaKeyword(): ?string
    {
        return $this->getData(self::META_KEYWORD);
    }

    /**
     * Set meta keyword
     *
     * @param string $metaKeyword
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->setData(self::META_KEYWORD, $metaKeyword);
    }


    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }


    /**
     * Get tax class id
     *
     * @return string|null
     */
    public function getTaxClassId(): ?string
    {
        return $this->getData(self::TAX_CLASS_ID);
    }


    /**
     * Set tax class id
     *
     * @param string $taxClassId
     */
    public function setTaxClassId($taxClassId)
    {
        $this->setData(self::TAX_CLASS_ID, $taxClassId);
    }


    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }


    /**
     * Set created at
     *
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }


    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }


    /**
     * Set updated at
     *
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }


    /**
     * Get url key
     *
     * @return string|null
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }


    /**
     * Set url key
     *
     * @param string $urlKey
     */
    public function setUrlKey($urlKey)
    {
        $this->setData(self::URL_KEY, $urlKey);
    }


    /**
     * Get Visibility
     *
     * @return string|null
     */
    public function getVisibility(): ?string
    {
        return $this->getData(self::VISIBILITY);
    }


    /**
     * Set Visibility
     *
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->setData(self::VISIBILITY, $visibility);
    }


    /**
     * Get weight
     *
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->getData(self::WEIGHT);
    }


    /**
     * Set weight
     *
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->setData(self::WEIGHT, $weight);
    }


    /**
     * Get weight unit
     *
     * @return string|null
     */
    public function getWeightUnit(): ?string
    {
        return $this->getData(self::WEIGHT_UNIT);
    }


    /**
     * Set weight unit
     *
     * @param string $weightUnit
     */
    public function setWeightUnit($weightUnit)
    {
        $this->setData(self::WEIGHT_UNIT, $weightUnit);
    }


    /**
     * Get currency
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->getData(self::CURRENCY);
    }


    /**
     * Set currency
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->setData(self::CURRENCY, $currency);
    }


    /**
     * Get Displayable
     *
     * @return bool|null
     */
    public function getDisplayable(): ?bool
    {
        return $this->getData(self::DISPLAYABLE);
    }


    /**
     * Set displayable
     *
     * @param bool $displayable
     */
    public function setDisplayable($displayable)
    {
        $this->setData(self::DISPLAYABLE, $displayable);
    }


    /**
     * Get buyable
     *
     * @return bool|null
     */
    public function getBuyable(): ?bool
    {
        return $this->getData(self::BUYABLE);
    }


    /**
     * Set buyable
     *
     * @param bool $buyable
     */
    public function setBuyable($buyable)
    {
        $this->setData(self::BUYABLE, $buyable);
    }


    /**
     * Get attributes
     *
     * @return \Magento\CatalogExportApi\Api\Data\AttributeInterface[]|mixed
     */
    public function getAttributes()
    {
        return $this->getData(self::ATTRIBUTES);
    }


    /**
     * Set attributes
     *
     * @param \Magento\CatalogExportApi\Api\Data\AttributeInterface[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this->setData(self::ATTRIBUTES, $attributes);
    }

    /**
     * Get Categories
     *
     * @return mixed|string[]
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }

    /**
     * Set categories
     *
     * @param string[] $categories
     */
    public function setCategories($categories)
    {
        $this->setData(self::CATEGORIES, $categories);
    }

    /**
     * Get options
     *
     * @return \Magento\CatalogExportApi\Api\Data\CustomOption[]|mixed
     */
    public function getOptions()
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * Set Options
     *
     * @param \Magento\CatalogExportApi\Api\Data\CustomOption[] $options
     */
    public function setOptions($options)
    {
        $this->setData(self::OPTIONS, $options);
    }

    /**
     * Get entered options
     *
     * @return \Magento\CatalogExportApi\Api\Data\EnteredOption[]|mixed
     */
    public function getEnteredOptions()
    {
        return $this->getData(self::ENTERED_OPTIONS);
    }

    /**
     * Set entered options
     *
     * @param \Magento\CatalogExportApi\Api\Data\EnteredOption[] $options
     */
    public function setEnteredOptions($options)
    {
        $this->setData(self::ENTERED_OPTIONS, $options);
    }

    /**
     * Get media gallery
     *
     * @return bool|null
     */
    public function getMediaGallery() : ?array
    {
        return $this->getData(self::MEDIA_GALLERY);
    }

    /**
     * @inheritdoc
     */
    public function setMediaGallery(?array $mediaGallery) : void
    {
        $this->setData(self::MEDIA_GALLERY, $mediaGallery);
    }

    /**
     * @inheritDoc
     */
    public function getImage() : ?ProductImage
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setImage(?ProductImage $image) : void
    {
        $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getSmallImage() : ?ProductImage
    {
        return $this->getData(self::SMALL_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setSmallImage(?ProductImage $image) : void
    {
        $this->setData(self::SMALL_IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getThumbnail() : ?ProductImage
    {
        return $this->getData(self::THUMBNAIL);
    }

    /**
     * @inheritDoc
     */
    public function setThumbnail(?ProductImage $image) : void
    {
        $this->setData(self::THUMBNAIL, $image);
    }

    /**
     * @inheritDoc
     */
    public function getSwatchImage() : ?ProductImage
    {
        return $this->getData(self::SWATCH_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setSwatchImage(?ProductImage $image) : void
    {
        $this->setData(self::SWATCH_IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getInStock(): ?bool
    {
        return $this->getData(self::IN_STOCK);
    }

    /**
     * Set in stock
     *
     * @param bool $inStock
     */
    public function setInStock($inStock)
    {
        $this->setData(self::IN_STOCK, $inStock);
    }

    /**
     * Get low stock
     *
     * @return bool|null
     */
    public function getLowStock(): ?bool
    {
        return $this->getData(self::LOW_STOCK);
    }

    /**
     * Set low stock
     *
     * @param bool $lowStock
     */
    public function setLowStock($lowStock)
    {
        $this->setData(self::LOW_STOCK, $lowStock);
    }

    /**
     * Get url
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->getData(self::URL);
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->setData(self::URL, $url);
    }

    /**
     * Allow open amount
     *
     * @return bool|null
     */
    public function getAllowOpenAmount(): ?bool
    {
        return $this->getData(self::ALLOW_OPEN_AMOUNT);
    }

    /**
     * Allow open amount
     *
     * @param bool $allowOpenAmount
     */
    public function setAllowOpenAmount($allowOpenAmount)
    {
        $this->setData(self::ALLOW_OPEN_AMOUNT, $allowOpenAmount);
    }

    /**
     * Get gift card type
     *
     * @return string|null
     */
    public function getGiftCardType() :? string
    {
        return $this->getData(self::GIFT_CARD_TYPE);
    }

    /**
     * Set gift card type
     *
     * @param bool $giftCardType
     */
    public function setGiftCardType($giftCardType)
    {
        $this->setData(self::GIFT_CARD_TYPE, $giftCardType);
    }

    /**
     * Get Redeemable
     *
     * @return bool|null
     */
    public function getRedeemable() :? bool
    {
        return $this->getData(self::REDEEMABLE);
    }

    /**
     * Set Redeemble
     *
     * @param bool $redeemable
     */
    public function setRedeemable($redeemable)
    {
        $this->setData(self::REDEEMABLE, $redeemable);
    }

    /**
     * Get Sku type
     *
     * @return string|null
     */
    public function getSkuType() :? string
    {
        return $this->getData(self::SKU_TYPE);
    }

    /**
     * Set sku type
     *
     * @param string $skuType
     */
    public function setSkuType($skuType)
    {
        $this->setData(self::SKU_TYPE, $skuType);
    }

    /**
     * Get Weight type
     *
     * @return string|null
     */
    public function getWeightType() :? string
    {
        return $this->getData(self::WEIGHT_TYPE);
    }

    /**
     * Set Weight type
     *
     * @param string $weightType
     */
    public function setWeightType($weightType)
    {
        $this->setData(self::WEIGHT_TYPE, $weightType);
    }
}
