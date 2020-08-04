<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Generated from et_schema.xml. DO NOT EDIT!”
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * OptionValue entity
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OptionValue
{
    /** @var string */
    private $id;

    /** @var \Magento\CatalogExportApi\Api\Data\ProductPrice */
    private $price;

    /** @var string */
    private $priceType;

    /** @var string */
    private $label;

    /** @var int */
    private $sortOrder;

    /** @var bool */
    private $isDefault;

    /** @var string */
    private $sample;

    /** @var string */
    private $value;

    /** @var string */
    private $sku;

    /** @var string */
    private $defaultLabel;

    /** @var string */
    private $storeLabel;

    /** @var bool */
    private $useDefaultValue;

    /**
     * Get id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Get price
     *
     * @return \Magento\CatalogExportApi\Api\Data\ProductPrice
     */
    public function getPrice(): ?ProductPrice
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param \Magento\CatalogExportApi\Api\Data\ProductPrice $price
     * @return void
     */
    public function setPrice(?ProductPrice $price): void
    {
        $this->price = $price;
    }

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType(): ?string
    {
        return $this->priceType;
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return void
     */
    public function setPriceType(?string $priceType): void
    {
        $this->priceType = $priceType;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return void
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get is default
     *
     * @return bool
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return void
     */
    public function setIsDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * Get sample
     *
     * @return string
     */
    public function getSample(): ?string
    {
        return $this->sample;
    }

    /**
     * Set sample
     *
     * @param string $sample
     * @return void
     */
    public function setSample(?string $sample): void
    {
        $this->sample = $sample;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Set sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * Get default label
     *
     * @return string
     */
    public function getDefaultLabel(): ?string
    {
        return $this->defaultLabel;
    }

    /**
     * Set default label
     *
     * @param string $defaultLabel
     * @return void
     */
    public function setDefaultLabel(?string $defaultLabel): void
    {
        $this->defaultLabel = $defaultLabel;
    }

    /**
     * Get store label
     *
     * @return string
     */
    public function getStoreLabel(): ?string
    {
        return $this->storeLabel;
    }

    /**
     * Set store label
     *
     * @param string $storeLabel
     * @return void
     */
    public function setStoreLabel(?string $storeLabel): void
    {
        $this->storeLabel = $storeLabel;
    }

    /**
     * Get use default value
     *
     * @return bool
     */
    public function getUseDefaultValue(): ?bool
    {
        return $this->useDefaultValue;
    }

    /**
     * Set use default value
     *
     * @param bool $useDefaultValue
     * @return void
     */
    public function setUseDefaultValue(?bool $useDefaultValue): void
    {
        $this->useDefaultValue = $useDefaultValue;
    }
}
