<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Request criteria DTO for category search Storefront API
 *
 * @see \Magento\CatalogStorefrontApi\Api\CategoryInterface
 */
interface CategoryCriteriaInterface
{
    /**
     * Provide ids
     * [
     *     3, 4
     * ]
     *
     * @return array
     */
    public function getIds(): array;

    /**
     * List of scopes in format, e.g ['store' => 3]
     *
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * List of requested attributes. e.g. ['id', 'name', 'is_anchor', 'product_count']
     *
     * @return string[]
     */
    public function getAttributes(): array;
}
