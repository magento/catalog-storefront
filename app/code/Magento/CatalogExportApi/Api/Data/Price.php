<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Option price
 */
class Price
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
     * Get regular price
     *
     * @return float|null
     */
    public function getRegularPrice(): ?float
    {
        return $this->regularPrice;
    }

    /**
     * Set regular price
     *
     * @param float|null $regularPrice
     * @return void
     */
    public function setRegularPrice(?float $regularPrice): void
    {
        $this->regularPrice = $regularPrice;
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

    /**
     * Set final price
     *
     * @param float|null $finalPrice
     * @return void
     */
    public function setFinalPrice(?float $finalPrice): void
    {
        $this->finalPrice = $finalPrice;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}
