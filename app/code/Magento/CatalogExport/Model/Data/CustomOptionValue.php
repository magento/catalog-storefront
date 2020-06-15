<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Magento\CatalogExportApi\Api\Data\CustomOptionValueInterface;

/**
 *
 */
class CustomOptionValue extends AbstractModel implements CustomOptionValueInterface
{
    /**#@+
     * Constants
     */
    const KEY_ID = 'id';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_SKU = 'sku';
    const KEY_OPTION_TYPE_ID = 'option_type_id';
    const KEY_VALUE = 'value';
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
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->getData(self::KEY_PRICE_TYPE);
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }

    /**
     * Set Option type id
     *
     * @param int|null $optionTypeId
     * @return int
     */
    public function setOptionTypeId($optionTypeId)
    {
        return $this->setData(self::KEY_OPTION_TYPE_ID, $optionTypeId);
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getOptionTypeId()
    {
        return $this->getData(self::KEY_OPTION_TYPE_ID);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->getData(self::KEY_VALUE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value)
    {
        return $this->setData(self::KEY_VALUE, $value);
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
     * Get Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }
}
