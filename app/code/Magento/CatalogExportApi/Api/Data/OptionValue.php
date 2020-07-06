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
//TODO: Change name to OptionValue..
class OptionValue
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
     * @var int|null
     */
    private $sortOrder;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\Price
     */
    private $price;

    /**
     * @var bool
     */
    private $isDefault;

    /**
     * @var int
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
     * Get Label
     *
     * @return ?string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set Label
     *
     * @param string $label
     * @return void
     */
    public function setLabel($label): void
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
     * Get option is default
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set option is default
     *
     * @param bool $isDefault
     * @return void
     */
    public function setIsDefault($isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * Get option value
     *
     * @return ?int
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Set option value
     *
     * @param int $value
     * @return void
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
