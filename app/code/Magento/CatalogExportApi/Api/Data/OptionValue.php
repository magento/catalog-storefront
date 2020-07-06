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
     * @var int
     */
    private $sortOrder;

    /**
     * @var Price
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
     * @var string
     */
    private $sample;

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
     * @return Price|null
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * Set option value price
     *
     * @param Price|null $price
     * @return void
     */
    public function setPrice(?Price $price): void
    {
        $this->price = $price;
    }

    /**
     * Get option is default
     *
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * Set option is default
     *
     * @param bool|null $isDefault
     * @return void
     */
    public function setIsDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * Get option value
     *
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Set option value
     *
     * @param int|null $value
     * @return void
     */
    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    /**
     * Get option sample
     *
     * @return string|null
     */
    public function getSample(): ?string
    {
        return $this->sample;
    }

    /**
     * Set option sample
     *
     * @param string|null $sample
     * @return void
     */
    public function setSample(?string $sample): void
    {
        $this->sample = $sample;
    }
}
