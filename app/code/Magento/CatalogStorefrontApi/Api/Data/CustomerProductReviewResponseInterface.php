<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Autogenerated description for CustomerProductReviewResponse interface
 *
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
interface CustomerProductReviewResponseInterface
{
    /**
     * Autogenerated description for getItems() interface method
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\ReadReviewInterface[]
     */
    public function getItems(): array;

    /**
     * Autogenerated description for setItems() interface method
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\ReadReviewInterface[] $value
     * @return void
     */
    public function setItems(array $value): void;

    /**
     * Autogenerated description for getPagination() interface method
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface|null
     */
    public function getPagination(): ?\Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface;

    /**
     * Autogenerated description for setPagination() interface method
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface $value
     * @return void
     */
    public function setPagination(\Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface $value): void;
}