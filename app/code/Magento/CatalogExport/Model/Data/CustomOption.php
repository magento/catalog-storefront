<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

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
    private $product_sku;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var bool
     */
    private $multi;

    /**
     * @var string
     */
    private $render_type;

    /***
     * @var int
     */
    private $sort_order;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Magento\CatalogExport\Model\Data\CustomOptionValue[]|null
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
        return $this->product_sku;
    }

    /**
     * Set product sku
     *
     * @param string $productSku
     * @return void
     */
    public function setProductSku(string $productSku)
    {
        $this->product_sku = $productSku;
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
        return (bool)$this->multi;
    }

    /**
     * Set multi
     *
     * @param bool $multi
     * @return void
     */
    public function setIsMulti(bool $multi): void
    {
        $this->multi = $multi;
    }

    /**
     * Return render type
     *
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->render_type;
    }

    /**
     * Set render type
     *
     * @param string $renderType
     * @return void
     */
    public function setRenderType(string $renderType): void
    {
        $this->render_type = $renderType;
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
     * @param int|null $sortOrder
     * @return void
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sort_order = $sortOrder;
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
     * @return \Magento\CatalogExport\Model\Data\CustomOptionValue[]|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Set option values
     *
     * @param \Magento\CatalogExport\Model\Data\CustomOptionValue[] $values
     * @return void
     */
    public function setValues(array $values = null)
    {
        $this->values = $values;
    }
}
