<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Interface for attribute values
 */
interface AttributeValueInterface
{
    /**
     * Get attribute id
     *
     * @return string|null
     */
    public function getId() :? string;

    /**
     * Set attribute id
     *
     * @param string|null $id
     * @return void
     */
    public function setId($id = null);

    /**
     * Get attribute value
     *
     * @return string|null
     */
    public function getValue() :? string;

    /**
     * Set attribute value
     *
     * @param string|null $value
     * @return void
     */
    public function setValue($value = null);
}
