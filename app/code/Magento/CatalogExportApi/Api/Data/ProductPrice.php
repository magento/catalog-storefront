<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Option price
 */
class ProductPrice
{
    /**
     * @var float|null
     */
    private $regularPrice;

    /**
     * @var float|null
     */
    private $finalPrice;

    /**
     * @param float|null $regularPrice
     * @param float|null $finalPrice
     */
    public function __construct(?float $regularPrice, ?float $finalPrice)
    {
        $this->regularPrice = $regularPrice;
        $this->finalPrice = $finalPrice;
    }

    /**
     * Get regular price
     *
     * @return float|null
     */
    public function getRegularPrice(): ?float
    {
        return $this->regularPrice;
    }

    /**
     * Get final price
     *
     * @return float|null
     */
    public function getFinalPrice(): ?float
    {
        return $this->finalPrice;
    }
}
