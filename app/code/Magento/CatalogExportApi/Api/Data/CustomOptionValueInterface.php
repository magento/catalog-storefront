<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Custom Option Value interface
 */
interface CustomOptionValueInterface
{
    /**
     * Get option value title
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Set option value title
     *
     * @param string $value
     * @return $this
     */
    public function setValue(string $value);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get option value price
     *
     * @return float[]
     */
    public function getPrice();

    /**
     * Set option value price
     *
     * @param array $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * Get Sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Option type id
     *
     * @return int
     */
    public function getOptionTypeId();

    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int
     */
    public function setOptionTypeId($optionTypeId);
}
