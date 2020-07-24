<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Generated from et_schema.xml. DO NOT EDIT!”
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

use Magento\CatalogExportApi\Api\Data\ProductPrice;

/**
 * PriceRange entity
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PriceRange
{
    /** @var \Magento\CatalogExportApi\Api\Data\ProductPrice */
    /** @var ProductPrice */
    private $minimumPrice;

    /** @var ProductPrice */
    private $maximumPrice;

    /**
     * Get minimum price
     *
     * @return \Magento\CatalogExportApi\Api\Data\ProductPrice
     */
    public function getMinimumPrice(): ?ProductPrice
    {
        return $this->minimumPrice;
    }

    /**
     * Set minimum price
     *
     * @param \Magento\CatalogExportApi\Api\Data\ProductPrice $minimumPrice
     * @return void
     */
    public function setMinimumPrice(?ProductPrice $minimumPrice): void
    {
        $this->minimumPrice = $minimumPrice;
    }

    /**
     * Get maximum price
     *
     * @return \Magento\CatalogExportApi\Api\Data\ProductPrice
     */
    public function getMaximumPrice(): ?ProductPrice
    {
        return $this->maximumPrice;
    }

    /**
     * Set maximum price
     *
     * @param \Magento\CatalogExportApi\Api\Data\ProductPrice $maximumPrice
     * @return void
     */
    public function setMaximumPrice(?ProductPrice $maximumPrice): void
    {
        $this->maximumPrice = $maximumPrice;
    }
}

