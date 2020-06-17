<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * EnteredOptions interface.
 */
interface EnteredOptionInterface
{
    /**
     * Get option type
     *
     * @return string
     */
    public function getType();

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Return render type
     *
     * @return string
     */
    public function getRenderType(): string;

    /**
     * Set render type
     *
     * @param string $renderType
     * @return $this
     */
    public function setRenderType(string $renderType);

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRequired();

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setRequired($isRequired);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get option id
     *
     * @return int
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Set product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku);

    /**
     * Get option value price
     *
     * @return float[]
     */
    public function getPrice();

    /**
     * Set option value price
     *
     * @param array $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * Get option value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get Sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Get file extension
     *
     * @return string
     */
    public function getFileExtension();

    /**
     * Get Max Characters
     *
     * @return int|null
     */
    public function getMaxCharacters();

    /**
     * Get image size X
     *
     * @return int|null
     */
    public function getImageSizeX();

    /**
     * Get image size Y
     *
     * @return int|null
     */
    public function getImageSizeY();

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Set File Extension
     *
     * @param string $fileExtension
     * @return $this
     */
    public function setFileExtension($fileExtension);

    /**
     * Set Max Characters
     *
     * @param int $maxCharacters
     * @return $this
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * Set Image Size X
     *
     * @param int $imageSizeX
     * @return $this
     */
    public function setImageSizeX($imageSizeX);

    /**
     * Set Image Size Y
     *
     * @param int $imageSizeY
     * @return $this
     */
    public function setImageSizeY($imageSizeY);
}
