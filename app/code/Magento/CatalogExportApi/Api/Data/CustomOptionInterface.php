<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogExportApi\Api\Data;

/**
 * CustomOption entity interface.
 */
interface CustomOptionInterface
{
    /**
     * Get option ID
     *
     * @return ?int
     */
    public function getId(): ?int;

    /**
     * Set option ID
     *
     * @param ?int $id
     * @return void
     */
    public function setId($id);

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return void
     */
    public function setProductSku($sku);

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return void
     */
    public function setOptionId($optionId);

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * Get option type
     *
     * @return string
     */
    public function getType();

    /**
     * Set option type
     *
     * @param string $type
     * @return void
     */
    public function setType($type);

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
     * @return void
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRequired();

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return void
     */
    public function setRequired($isRequired);

    /**
     * Get option values
     *
     * @return \Magento\CatalogExportApi\Api\Data\CustomOptionValueInterface[]|null
     */
    public function getValues();

    /**
     * Set option values
     *
     * @param \Magento\CatalogExportApi\Api\Data\CustomOptionValueInterface[] $values
     * @return void
     */
    public function setValues(array $values = null);

    /**
     * Return render type
     *
     * @return string
     */
    public function getRenderType(): string;

    /**
     * Set render type
     *
     * @param string $renderType
     * @return void
     */
    public function setRenderType(string $renderType);

    /**
     * Get is multi
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsMulti(): bool;

    /**
     * Set multi
     *
     * @param bool $multi
     * @return void
     */
    public function setIsMulti(bool $multi);
}
