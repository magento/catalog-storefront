<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExportApi\Api\Data\AttributeInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Product attribute entity
 */
class Attribute extends AbstractModel implements AttributeInterface
{
    private const ATTRIBUTE_CODE = 'attribute_code';

    private const TYPE = 'type';

    private const VALUE = 'value';

    /**
     * @return string
     */
    public function getAttributeCode() : string
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @param string $attributeCode
     */
    public function setAttributeCode($attributeCode)
    {
        $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @return \Magento\CatalogExportApi\Api\Data\AttributeValueInterface[]|mixed
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @param \Magento\CatalogExportApi\Api\Data\AttributeValueInterface[] $value
     */
    public function setValue($value)
    {
        $this->setData(self::VALUE, $value);
    }
}
