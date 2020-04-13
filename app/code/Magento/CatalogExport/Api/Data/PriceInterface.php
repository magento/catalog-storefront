<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

interface PriceInterface
{
    /**
     * @return string
     */
    public function getCode() : string;

    /**
     * @param string $code
     * @return void
     */
    public function setCode($code);

    /**
     * @return float
     */
    public function getRegularPrice() :? float;

    /**
     * @param float $regularPrice
     * @return void
     */
    public function setRegularPrice($regularPrice);

    /**
     * @return float
     */
    public function getFinalPrice() :? float;

    /**
     * @param float $finalPrice
     * @return void
     */
    public function setFinalPrice($finalPrice);
}