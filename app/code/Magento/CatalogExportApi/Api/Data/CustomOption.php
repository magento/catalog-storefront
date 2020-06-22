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
class CustomOption
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var bool
     */
    private $isMulti;

    /**
     * @var string
     */
    private $renderType;

    /***
     * @var int
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\CustomOptionValue[]|null
     */
    private $values;

    /**
     * Get option id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set option id
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Get sku of the product
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * Set product sku
     *
     * @param string $productSku
     * @return void
     */
    public function setProductSku(string $productSku)
    {
        $this->productSku = $productSku;
    }

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return void
     */
    public function setRequired($isRequired): void
    {
        $this->required = $isRequired;
    }

    /**
     * Get is multi
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsMulti(): bool
    {
        return (bool)$this->isMulti;
    }

    /**
     * Set multi
     *
     * @param bool $multi
     * @return void
     */
    public function setIsMulti(bool $multi): void
    {
        $this->isMulti = $multi;
    }

    /**
     * Return render type
     *
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }

    /**
     * Set render type
     *
     * @param string $renderType
     * @return void
     */
    public function setRenderType(string $renderType): void
    {
        $this->renderType = $renderType;
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
     * Get option values
     *
     * @return \Magento\CatalogExportApi\Api\Data\CustomOptionValue[]|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Set option values
     *
     * @param \Magento\CatalogExportApi\Api\Data\CustomOptionValue[] $values
     * @return void
     */
    public function setValues(array $values = null): void
    {
        $this->values = $values;
    }
}
