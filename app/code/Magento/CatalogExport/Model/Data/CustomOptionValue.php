<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

/**
 * Custom option value entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomOptionValue
{
    /**#@+
     * Constants
     */
    const KEY_ID = 'id';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_SKU = 'sku';
    const KEY_VALUE = 'value';
    /**#@-*/

    /**
     * @var int
     */
    private $id;

    /**
     * @var int|null
     */
    private $sort_order;

    /**
     * @var float[]
     */
    private $price;

    /**
     * @var string
     */
    private $price_type;

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
    public function setId(int $id): void
    {
        $this->id = $id;
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
        return $this->sort_order;
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder($sortOrder): void
    {
        $this->sort_order = $sortOrder;
    }

    /**
     * Get option value price
     *
     * @return float[]
     */
    public function getPrice(): array
    {
        return $this->price;
    }

    /**
     * Set option value price
     *
     * @param array $price
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
        return $this->price_type;
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return void
     */
    public function setPriceType($priceType): void
    {
        $this->price_type = $priceType;
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
