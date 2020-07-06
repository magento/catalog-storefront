<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Custom option entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Option
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /***
     * @var int
     */
    private $sortOrder;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\OptionValue[]
     */
    private $values;

    /**
     * Get option type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set option type
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set option id
     *
     * @param int|null $id
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get option label
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set option label
     *
     * @param string|null $label
     * @return void
     */
    public function setLabel(?string $label)
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
     * Get is required
     *
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }

    /**
     * Set is required
     *
     * @param bool|null $isRequired
     * @return void
     */
    public function setIsRequired(?bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * Get option values
     *
     * @return \Magento\CatalogExportApi\Api\Data\OptionValue[]|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Set option values
     *
     * @param \Magento\CatalogExportApi\Api\Data\OptionValue[]|null $values
     * @return void
     */
    public function setValues(?array $values = null): void
    {
        $this->values = $values;
    }
}
