<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Autogenerated description for DeleteProductsRequestInterface interface
 *
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
interface DeleteProductsRequestInterface
{
    /**
     * Autogenerated description for getProducts() interface method
     *
     * @return int[]
     */
    public function getProductIds(): array;

    /**
     * Autogenerated description for setProducts() interface method
     *
     * @param int[] array
     * @return void
     */
    public function setProductIds(array $ids): void;

    /**
     * Autogenerated description for getStore() interface method
     *
     * @return int
     */
    public function getStore(): int;

    /**
     * Autogenerated description for setStore() interface method
     *
     * @param int $store
     * @return void
     */
    public function setStore(int $store): void;
}
