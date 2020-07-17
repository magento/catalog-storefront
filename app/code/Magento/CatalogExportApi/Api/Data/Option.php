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
 * Option entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Option
{
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string */
    private $renderType;

    /** @var bool */
    private $required;

    /** @var string */
    private $title;

    /** @var int */
    private $sortOrder;

    /** @var string */
    private $productSku;

    /** @var \Magento\CatalogExportApi\Api\Data\OptionValue[]|null */
    private $values;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get render type
     *
     * @return string
     */
    public function getRenderType(): ?string
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
     * Get required
     *
     * @return bool
     */
    public function getRequired(): ?bool
    {
        return $this->required;
    }

    /**
     * Set required
     *
     * @param bool $required
     * @return void
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get product sku
     *
     * @return string
     */
    public function getProductSku(): ?string
    {
        return $this->productSku;
    }

    /**
     * Set product sku
     *
     * @param string $productSku
     * @return void
     */
    public function setProductSku(string $productSku): void
    {
        $this->productSku = $productSku;
    }

    /**
     * Get values
     *
     * @return \Magento\CatalogExportApi\Api\Data\OptionValue[]|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Set values
     *
     * @param \Magento\CatalogExportApi\Api\Data\OptionValue[] $values
     * @return void
     */
    public function setValues(array $values = null): void
    {
        $this->values = $values;
    }
}
