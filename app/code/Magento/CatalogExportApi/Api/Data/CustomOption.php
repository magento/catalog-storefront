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
     * Super Option Type
     */
    public const SUPER_OPTION_TYPE = 'super';

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
    private $title;

    /**
     * @var string
     */
    private $productSku;

    /***
     * @var int
     */
    private $sortOrder;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var string
     */
    private $renderType;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\CustomOptionValue[]
     */
    private $values;

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
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
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int|null $id
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string|null $title
     * @return void
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get product sku
     *
     * @return string|null
     */
    public function getProductSku(): ?string
    {
        return $this->productSku;
    }

    /**
     * Set product sku
     *
     * @param string|null $productSku
     * @return void
     */
    public function setProductSku(?string $productSku): void
    {
        $this->productSku = $productSku;
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
     * Get required
     *
     * @return bool|null
     */
    public function getRequired(): ?bool
    {
        return $this->required;
    }

    /**
     * Set required
     *
     * @param bool|null $required
     * @return void
     */
    public function setRequired(?bool $required): void
    {
        $this->required = $required;
    }

    /**
     * Return render type
     *
     * @return string|null
     */
    public function getRenderType(): ?string
    {
        return $this->renderType;
    }

    /**
     * Set render type
     *
     * @param string|null $renderType
     * @return void
     */
    public function setRenderType(?string $renderType): void
    {
        $this->renderType = $renderType;
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
     * @param \Magento\CatalogExportApi\Api\Data\CustomOptionValue[]|null $values
     * @return void
     */
    public function setValues(?array $values = null): void
    {
        $this->values = $values;
    }
}
