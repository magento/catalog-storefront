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
     * @var int|null
     */
    private $sortOrder;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\Price
     */
    private $price;

    /**
     * @var string
     */
    private $priceType;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $value;

    /**
     * Get option value ID
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set option value ID
     *
     * @param int $id
     * @return void
     */
    public function setId($id): void
    {
        $this->id = (int)$id;
    }

    /**
     * Get option value title
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set option value title
     *
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder($sortOrder): void
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
     * @return string
     */
    public function getPriceType(): string
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
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku($sku): void
    {
        $this->sku = $sku;
    }
}
