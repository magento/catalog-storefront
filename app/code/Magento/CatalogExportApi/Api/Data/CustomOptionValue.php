<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Custom option value entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomOptionValue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var bool
     */
    private $isDefault;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $sample;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\Price
     */
    private $price;

    /**
     * @var string
     */
    private $priceType;

    /**
     * Get ID
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return void
     */
    public function setId($id): void
    {
        $this->id = (int)$id;
    }

    /**
     * Get Label
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set Label
     *
     * @param string|null $label
     * @return void
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * Get sort order
     *
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param int|null $sortOrder
     * @return void
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get option value price
     *
     * @return \Magento\CatalogExportApi\Api\Data\Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set option value price
     *
     * @param \Magento\CatalogExportApi\Api\Data\Price $price
     * @return void
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * Get price type
     *
     * @return string|null
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
    public function setPriceType($priceType): void
    {
        $this->priceType = $priceType;
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Set Sku
     *
     * @param string|null $sku
     * @return void
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * Get is default
     *
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * Set is default
     *
     * @param bool|null $isDefault
     * @return void
     */
    public function setIsDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * Get value
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string|null $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * Get sample
     *
     * @return string|null
     */
    public function getSample(): ?string
    {
        return $this->sample;
    }

    /**
     * Set sample
     *
     * @param string|null $sample
     * @return void
     */
    public function setSample(?string $sample): void
    {
        $this->sample = $sample;
    }
}
