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
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
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
    public function setProducts(array $products = null): void
    {
        $this->products = $products;
    }
}
