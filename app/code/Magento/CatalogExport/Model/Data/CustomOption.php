<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExport\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Magento\CatalogExportApi\Api\Data\CustomOptionInterface;

/**
 * Custom option entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomOption extends AbstractModel implements CustomOptionInterface
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
    /**#@-*/

    /**
     * @inheritDoc
     */
    public function getId(): ?int
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
    public function getIsMulti(): bool
    {
        return (bool)$this->getData(self::KEY_MULTI);
    }

    /**
     * @inheritDoc
     */
    public function setIsMulti(bool $multi)
    {
        $this->setData(self::KEY_MULTI, $multi);
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
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        $this->setData(self::KEY_TITLE, $title);
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
    public function setProductSku($productSku)
    {
        $this->setData(self::KEY_PRODUCT_SKU, $productSku);
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
        $this->setData(self::KEY_VALUES, $values);
    }
}
