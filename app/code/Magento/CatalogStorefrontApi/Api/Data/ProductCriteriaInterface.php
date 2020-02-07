<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Request criteria DTO for product Storefront API
 *
 * @see \Magento\CatalogStorefrontApi\Api\ProductInterface
 */
interface ProductCriteriaInterface
{
    /**
     * Product ids in format [id1, id2, ...]
     *
     * @return string[]
     */
    public function getIds(): array;

    /**
     * List of scopes in format, e.g ['store' => 3]
     *
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * List of requested attributes. e.g. ['id', 'sku', 'name', 'url_key']
     *
     * @return string[]
     */
    public function getAttributes(): array;
}
