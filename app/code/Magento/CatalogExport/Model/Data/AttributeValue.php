<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExportApi\Api\Data\AttributeValueInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Attribute value entity
 */
class AttributeValue extends AbstractModel implements AttributeValueInterface
{
    private const ID = 'id';

    private const VALUE = 'value';

    /**
     * @inheritdoc
     */
    public function getId() :? string
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id = null)
    {
        $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getValue() :? string
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value = null)
    {
        $this->setData(self::VALUE, $value);
    }
}
