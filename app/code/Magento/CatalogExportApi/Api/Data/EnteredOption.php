<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Entered option entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class EnteredOption
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var bool
     */
    private $required;

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
     * @var string
     */
    private $sku;

    /**
     * @var ?string
     */
    private $value;

    /**
     * @var ?string
     */
    private $fileExtension;

    /**
     * @var ?int
     */
    private $maxCharacters;

    /**
     * @var ?int
     */
    private $imageSizeX;

    /**
     * @var ?int
     */
    private $imageSizeY;

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
     * Get option value
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set option value
     *
     * @param string|null $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * Get Sku
     *
     * @return ?string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * Get required
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set required
     *
     * @param bool $required
     * @return void
     */
    public function setRequired($required): void
    {
        $this->required = $required;
    }

    /**
     * Get option type
     *
     * @return ?string
     */
    public function getType(): ?string
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
     * Get file extension
     *
     * @return string|null
     */
    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    /**
     * Set File Extension
     *
     * @param string|null $fileExtension
     * @return void
     */
    public function setFileExtension(?string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Get Max Characters
     *
     * @return int|null
     */
    public function getMaxCharacters(): ?int
    {
        return $this->maxCharacters;
    }

    /**
     * Set Max Characters
     *
     * @param int|null $maxCharacters
     * @return void
     */
    public function setMaxCharacters(?int $maxCharacters): void
    {
        $this->maxCharacters = $maxCharacters;
    }

    /**
     * Get image size X
     *
     * @return int|null
     */
    public function getImageSizeX(): ?int
    {
        return $this->imageSizeX;
    }

    /**
     * Set Image Size X
     *
     * @param int|null $imageSizeX
     * @return void
     */
    public function setImageSizeX(?int $imageSizeX): void
    {
        $this->imageSizeX = $imageSizeX;
    }

    /**
     * Get image size Y
     *
     * @return int|null
     */
    public function getImageSizeY(): ?int
    {
        return $this->imageSizeY;
    }

    /**
     * Set Image Size Y
     *
     * @param int|null $imageSizeY
     * @return void
     */
    public function setImageSizeY(?int $imageSizeY): void
    {
        $this->imageSizeY = $imageSizeY;
    }
}
