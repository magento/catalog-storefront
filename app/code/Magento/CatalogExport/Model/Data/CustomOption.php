<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\CatalogExportApi\Api\Data\CustomOptionsInterface;

/**
 *
 */
class CustomOption extends AbstractExtensibleModel implements CustomOptionsInterface
{
    /**#@+
     * Constants
     */
    const KEY_ID = 'id';
    const KEY_PRODUCT_SKU = 'product_sku';
    const KEY_OPTION_ID = 'option_id';
    const KEY_TITLE = 'title';
    const KEY_TYPE = 'type';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_REQUIRED = 'required';
    const KEY_RENDER_TYPE = 'render_type';
    const KEY_MULTI = 'multi';
    const KEY_VALUES = 'values';

    // option value
    //const KEY_PRICE = 'price';
    //const KEY_PRICE_TYPE = 'price_type';
    //const KEY_SKU = 'sku';

    //
//    const KEY_FILE_EXTENSION = 'file_extension';
//    const KEY_MAX_CHARACTERS = 'max_characters';
//    const KEY_IMAGE_SIZE_Y = 'image_size_y';
//    const KEY_IMAGE_SIZE_X = 'image_size_x';
    /**#@-*/


    /**
     * Get product option id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * Set product option id
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setData(self::KEY_ID, $value);
    }

    /**
     * Get option type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Return render type
     *
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->getData(self::KEY_RENDER_TYPE);
    }

    /**
     * Set render type
     *
     * @param string $renderType
     * @return $this
     */
    public function setRenderType(string $renderType)
    {
        return $this->setData(self::KEY_RENDER_TYPE, $renderType);
    }

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRequired()
    {
        return $this->getData(self::KEY_REQUIRED);
    }

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setRequired($isRequired)
    {
        return $this->setData(self::KEY_REQUIRED, $isRequired);
    }

    /**
     * @return bool
     */
    public function getIsMulti(): bool
    {
        return (bool)$this->getData(self::KEY_MULTI);
    }

    /**
     * Set multi
     *
     * @param bool $multi
     * @return $this
     */
    public function setIsMulti(bool $multi)
    {
        return $this->setData(self::KEY_MULTI, $multi);
    }

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Get option id
     *
     * @return int|null
     * @codeCoverageIgnoreStart
     */
    public function getOptionId()
    {
        return $this->getData(self::KEY_OPTION_ID);
    }

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::KEY_OPTION_ID, $optionId);
    }


    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->getData(self::KEY_PRODUCT_SKU);
    }

    /**
     * Set product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::KEY_PRODUCT_SKU, $productSku);
    }


    /**
     * @inheritDoc
     */
    public function getValues()
    {
        return $this->getData(self::KEY_VALUES);
    }

    /**
     * @inheritDoc
     */
    public function setValues(array $values = null)
    {
        return $this->setData(self::KEY_VALUES, $values);
    }


    /**
     * Get price type
     *
     * @return string|null
     */
//    public function getPriceType()
//    {
//        return $this->getData(self::KEY_PRICE_TYPE);
//    }

    /**
     * Get Sku
     *
     * @return string|null
     */
//    public function getSku()
//    {
//        return $this->getData(self::KEY_SKU);
//    }

    /**
     * Get file extension
     *
     * @return string|null
     */
//    public function getFileExtension()
//    {
//        return $this->getData(self::KEY_FILE_EXTENSION);
//    }

    /**
     * Get Max Characters
     *
     * @return int|null
     */
//    public function getMaxCharacters()
//    {
//        return $this->getData(self::KEY_MAX_CHARACTERS);
//    }

    /**
     * Get image size X
     *
     * @return int|null
     */
//    public function getImageSizeX()
//    {
//        return $this->getData(self::KEY_IMAGE_SIZE_X);
//    }

    /**
     * Get image size Y
     *
     * @return int|null
     */
//    public function getImageSizeY()
//    {
//        return $this->getData(self::KEY_IMAGE_SIZE_Y);
//    }


    /**
     * Return price. If $flag is true and price is percent
     *
     * Return converted percent to price
     *
     * @param bool $flag
     * @return float
     */
//    public function getPrice()
//    {
//        return $this->getData(self::KEY_PRICE);
//    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
//    public function setPrice($price)
//    {
//        return $this->setData(self::KEY_PRICE, $price);
//    }


    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
//    public function setPriceType($priceType)
//    {
//        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
//    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
//    public function setSku($sku)
//    {
//        return $this->setData(self::KEY_SKU, $sku);
//    }

    /**
     * Set File Extension
     *
     * @param string $fileExtension
     * @return $this
     */
//    public function setFileExtension($fileExtension)
//    {
//        return $this->setData(self::KEY_FILE_EXTENSION, $fileExtension);
//    }

    /**
     * Set Max Characters
     *
     * @param int $maxCharacters
     * @return $this
     */
//    public function setMaxCharacters($maxCharacters)
//    {
//        return $this->setData(self::KEY_MAX_CHARACTERS, $maxCharacters);
//    }

    /**
     * Set Image Size X
     *
     * @param int $imageSizeX
     * @return $this
     */
//    public function setImageSizeX($imageSizeX)
//    {
//        return $this->setData(self::KEY_IMAGE_SIZE_X, $imageSizeX);
//    }

    /**
     * Set Image Size Y
     *
     * @param int $imageSizeY
     * @return $this
     */
//    public function setImageSizeY($imageSizeY)
//    {
//        return $this->setData(self::KEY_IMAGE_SIZE_Y, $imageSizeY);
//    }
}
