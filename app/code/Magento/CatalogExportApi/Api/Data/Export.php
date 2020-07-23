<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Generated from et_schema.xml. DO NOT EDIT!”
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Export entity
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Export
{
    /** @var \Magento\CatalogExportApi\Api\Data\Product[]|null */
    private $products;

    /**
     * Get products
     *
     * @return \Magento\CatalogExportApi\Api\Data\Product[]|null
     */
    public function getProducts(): ?array
    {
        return $this->products;
    }

    /**
     * Set products
     *
     * @param \Magento\CatalogExportApi\Api\Data\Product[] $products
     * @return void
     */
    public function setProducts(?array $products = null): void
    {
        $this->products = $products;
    }
}
