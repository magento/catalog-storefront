<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Magento\CatalogExportApi\Api\Data\EnteredOptionInterface;

/**
 * Entered option entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class EnteredOption extends AbstractModel implements EnteredOptionInterface
{
    /**#@+
     * Constants
     */
    const KEY_VALUE = 'value';
    const KEY_REQUIRED = 'required';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_TYPE = 'type';
    const KEY_RENDER_TYPE = 'render_type';
    const KEY_OPTION_ID = 'option_id';
    const KEY_PRODUCT_SKU = 'product_sku';
    const KEY_SKU = 'sku';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_FILE_EXTENSION = 'file_extension';
    const KEY_MAX_CHARACTERS = 'max_characters';
    const KEY_IMAGE_SIZE_Y = 'image_size_y';
    const KEY_IMAGE_SIZE_X = 'image_size_x';
    /**#@-*/

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getRenderType(): string
    {
        return $this->getData(self::KEY_RENDER_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setRenderType(string $renderType)
    {
        $this->setData(self::KEY_RENDER_TYPE, $renderType);
    }

    /**
     * @inheritDoc
     */
    public function getRequired()
    {
        return $this->getData(self::KEY_REQUIRED);
    }

    /**
     * @inheritDoc
     */
    public function setRequired($isRequired)
    {
        $this->setData(self::KEY_REQUIRED, $isRequired);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getOptionId()
    {
        return $this->getData(self::KEY_OPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOptionId($optionId)
    {
        $this->setData(self::KEY_OPTION_ID, $optionId);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->getData(self::KEY_PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku($productSku)
    {
        $this->setData(self::KEY_PRODUCT_SKU, $productSku);
    }

    /**
     * @inheritDoc
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setPrice($price)
    {
        $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getPriceType()
    {
        return $this->getData(self::KEY_PRICE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setPriceType($priceType)
    {
        $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::KEY_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension()
    {
        return $this->getData(self::KEY_FILE_EXTENSION);
    }

    /**
     * @inheritDoc
     */
    public function getMaxCharacters()
    {
        return $this->getData(self::KEY_MAX_CHARACTERS);
    }

    /**
     * @inheritDoc
     */
    public function getImageSizeX()
    {
        return $this->getData(self::KEY_IMAGE_SIZE_X);
    }

    /**
     * @inheritDoc
     */
    public function getImageSizeY()
    {
        return $this->getData(self::KEY_IMAGE_SIZE_Y);
    }

    /**
     * @inheritDoc
     */
    public function setSku($sku)
    {
        $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * @inheritDoc
     */
    public function setFileExtension($fileExtension)
    {
        $this->setData(self::KEY_FILE_EXTENSION, $fileExtension);
    }

    /**
     * @inheritDoc
     */
    public function setMaxCharacters($maxCharacters)
    {
        $this->setData(self::KEY_MAX_CHARACTERS, $maxCharacters);
    }

    /**
     * @inheritDoc
     */
    public function setImageSizeX($imageSizeX)
    {
        $this->setData(self::KEY_IMAGE_SIZE_X, $imageSizeX);
    }

    /**
     * @inheritDoc
     */
    public function setImageSizeY($imageSizeY)
    {
        $this->setData(self::KEY_IMAGE_SIZE_Y, $imageSizeY);
    }
}
