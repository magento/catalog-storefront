<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

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
    private $product_sku;

    /**
     * @var bool
     */
    private $required;

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
     * @var string
     */
    private $sku;

    /**
     * @var float[]
     */
    private $price;

    /**
     * @var string
     */
    private $price_type;

    /**
     * @var ?string
     */
    private $value;

    /**
     * @var ?string
     */
    private $file_extension;

    /**
     * @var ?int
     */
    private $max_characters;

    /**
     * @var ?int
     */
    private $image_size_y;

    /**
     * @var ?int
     */
    private $image_size_x;

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
     * Get option value price
     *
     * @return float[]
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set option value price
     *
     * @param array $price
     * @return void
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->price_type;
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return void
     */
    public function setPriceType($priceType)
    {
        $this->price_type = $priceType;
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
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
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
     * Get file extension
     *
     * @return string|null
     */
    public function getFileExtension(): ?string
    {
        return $this->file_extension;
    }

    /**
     * Set File Extension
     *
     * @param string|null $fileExtension
     * @return void
     */
    public function setFileExtension(?string $fileExtension)
    {
        $this->file_extension = $fileExtension;
    }

    /**
     * Get Max Characters
     *
     * @return int|null
     */
    public function getMaxCharacters(): ?int
    {
        return $this->max_characters;
    }

    /**
     * Set Max Characters
     *
     * @param int|null $maxCharacters
     * @return void
     */
    public function setMaxCharacters(?int $maxCharacters): void
    {
        $this->max_characters = $maxCharacters;
    }

    /**
     * Get image size X
     *
     * @return int|null
     */
    public function getImageSizeX(): ?int
    {
        return $this->image_size_x;
    }

    /**
     * Set Image Size X
     *
     * @param int|null $imageSizeX
     * @return void
     */
    public function setImageSizeX(?int $imageSizeX): void
    {
        $this->image_size_x = $imageSizeX;
    }

    /**
     * Get image size Y
     *
     * @return int|null
     */
    public function getImageSizeY(): ?int
    {
        return $this->image_size_y;
    }

    /**
     * Set Image Size Y
     *
     * @param int|null $imageSizeY
     * @return void
     */
    public function setImageSizeY(?int $imageSizeY): void
    {
        $this->image_size_y = $imageSizeY;
    }
}
