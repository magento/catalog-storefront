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
 * ProductVariant entity
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductVariant
{
    /** @var string */
    private $id;

    /** @var array */
    private $optionValueId;

    /** @var \Magento\CatalogExportApi\Api\Data\Price[]|null */
    private $price;

    /**
     * Get id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Get option value id
     *
     * @return string[]
     */
    public function getOptionValueId(): ?array
    {
        return $this->optionValueId;
    }

    /**
     * Set option value id
     *
     * @param string[] $optionValueId
     * @return void
     */
    public function setOptionValueId(?array $optionValueId = null): void
    {
        $this->optionValueId = $optionValueId;
    }

    /**
     * Get price
     *
     * @return \Magento\CatalogExportApi\Api\Data\Price[]|null
     */
    public function getPrice(): ?array
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param \Magento\CatalogExportApi\Api\Data\Price[] $price
     * @return void
     */
    public function setPrice(?array $price = null): void
    {
        $this->price = $price;
    }
}
