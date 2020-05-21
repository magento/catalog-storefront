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

    private const VALUE = 'value';

    /**
     * @inheritdoc
     */
    public function getAttributeCode() : string
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setAttributeCode($attributeCode)
    {
        $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->setData(self::VALUE, $value);
    }
}
