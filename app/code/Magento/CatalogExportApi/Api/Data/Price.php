<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Generated from et_schema.xml. DO NOT EDIT!
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Price entity
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Price
{
    /** @var float */
    private $price;

    /** @var string */
    private $scope;

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return void
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return void
     */
    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }
}
