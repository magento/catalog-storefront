<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Entity attribute interface
 */
interface AttributeInterface
{
    /**
     * Get entity attribute code
     *
     * @return string
     */
    public function getAttributeCode() : string;

    /**
     * Set entity attribute code
     *
     * @param string $attributeCode
     * @return void
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get entity attribute value
     *
     * @return string[]
     */
    public function getValue();

    /**
     * Set entity attribute value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value);
}
