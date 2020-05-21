<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

interface AttributeInterface
{
    /**
     * @return string
     */
    public function getAttributeCode() : string;

    /**
     * @param string $attributeCode
     * @return void
     */
    public function setAttributeCode($attributeCode);

    /**
     * @return string[]
     */
    public function getValue();

    /**
     * @param string $value
     * @return void
     */
    public function setValue($value);
}
