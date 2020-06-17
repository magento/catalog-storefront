<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Magento\CatalogExportApi\Api\Data\CustomOptionValueInterface;

/**
 * Custom option value entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
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
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        $this->setData(self::KEY_ID, $value);
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
    public function setOptionTypeId($optionTypeId)
    {
        $this->setData(self::KEY_OPTION_TYPE_ID, $optionTypeId);
    }

    /**
     * @inheritDoc
     */
    public function getOptionTypeId()
    {
        return $this->getData(self::KEY_OPTION_TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->getData(self::KEY_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $value)
    {
        $this->setData(self::KEY_VALUE, $value);
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
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setSku($sku)
    {
        $this->setData(self::KEY_SKU, $sku);
    }
}
